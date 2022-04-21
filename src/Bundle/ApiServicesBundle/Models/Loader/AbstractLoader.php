<?php

namespace Cob\Bundle\ApiServicesBundle\Models\Loader;

use Cob\Bundle\ApiServicesBundle\Models\Config\ResponseModelConfig;
use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\ResponseModelPostExecuteCommandEvent;
use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\ResponseModelPostLoadEvent;
use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\ResponseModelPostLoadFromCacheEvent;
use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\ResponseModelPreExecuteCommandEvent;
use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\ResponseModelPreGetLoadCommandEvent;
use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\ResponseModelPreLoadEvent;
use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\ResponseModelPreLoadFromCacheEvent;
use Cob\Bundle\ApiServicesBundle\Models\ExceptionHandlers\ExceptionHandlerInterface;
use Cob\Bundle\ApiServicesBundle\Models\Loader\Config\LoadConfig;
use Cob\Bundle\ApiServicesBundle\Models\Response\ResponseModel;
use Cob\Bundle\ApiServicesBundle\Models\Util\CacheHash;
use Cob\Bundle\ApiServicesBundle\Models\Util\ClassUtil;
use Cob\Bundle\ApiServicesBundle\Models\Util\LogUtil;
use Cob\Bundle\ApiServicesBundle\Models\Util\Promise;
use GuzzleHttp\Command\CommandInterface;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Promise\PromiseInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractLoader implements LoaderInterface
{
    /**
     * Quickly obtain a response class after confirming it represents a {@link ResponseModel}.
     *
     * @param ResponseModelConfig $config     the response model config we want to get a new model for
     * @param LoadConfig          $loadConfig the load-time configuration to use when loading
     * @param LoadState           $loadState  the load state we desire the response model to be initialized with
     * @param PromiseInterface    $promise    the Promise the response model will use to obtain its data
     *
     * @return ResponseModel
     */
    protected static function getNewResponseClass(
        ResponseModelConfig $config,
        LoadConfig $loadConfig,
        LoadState $loadState,
        PromiseInterface $promise
    ): ResponseModel {
        $responseClass = $config->getResponseModelClass();

        ClassUtil::confirmValidResponseModel($responseClass);

        return new $responseClass(
            $loadConfig->getClient(),
            $loadState,
            $promise,
            $loadConfig->getParent()
        );
    }

    /**
     * Get the exception handler to use when loading data through the service client.
     *
     * The exception handler established in the load configuration is given priority. However, if none was set,
     * the default exception handler established for the response model in its configuration will be used.
     *
     * If neither configuration has explicitly provided an exception handler, the default is to wrap the exception in
     * our specific exception and pass the exception through.
     *
     * @param LoadConfig          $loadConfig the load configuration to consult for the exception handler
     * @param ResponseModelConfig $config     the response model's configuration whose default exception handler will be
     *                                        used if not specified in the load configuration
     *
     * @return ExceptionHandlerInterface the exception handler to use
     */
    protected static function getExceptionHandler(
        LoadConfig $loadConfig,
        ResponseModelConfig $config
    ): ExceptionHandlerInterface {
        return $loadConfig->hasExceptionHandler()
            ? $loadConfig->getExceptionHandler()
            : $config->getDefaultExceptionHandler();
    }

    /**
     * Get the {@link PromiseInterface} needed to load data for a given {@link LoadConfiguration}.
     *
     * @param ResponseModelConfig $config     the response model config to use when loading
     * @param LoadConfig          $loadConfig the load-time configuration to use when loading
     *
     * @return PromiseInterface
     */
    protected static function getLoadPromise(
        ResponseModelConfig $config,
        LoadConfig $loadConfig
    ): PromiseInterface
    {
        $responseModelClass = $config->getResponseModelClass();
        $client = $loadConfig->getClient();

        ClassUtil::confirmValidResponseModel($responseModelClass);

        return Promise::async(function () use ($client, $config, $loadConfig) {
            /**
             * @var ResponseModelPreLoadEvent $event
             */
            $client->dispatchEvent(
                ResponseModelPreLoadEvent::class,
                $config,
                $loadConfig->getCommandArgs()
            );
        })->then(function () use ($client, $config, $loadConfig) {
            //Can we load from cache?
            list($hash, $cache) = static::getCachedData($config, $loadConfig);

            if (!is_null($cache) && $cache !== false) {
                LogUtil::debug($client->getOutput(), 'Loading from cache!');
                return new FulfilledPromise($cache);
            }

            //Get the command based on our configs
            $command = static::getLoadCommand($config, $loadConfig);

            return static::getExecuteCommandPromise(
                $config,
                $loadConfig,
                $command,
                $hash
            )->wait();
        })->then(function ($response) use ($client, $config, $loadConfig) {
            /**
             * @var ResponseModelPostLoadEvent $event
             */
            $event = $client->dispatchEvent(
                ResponseModelPostLoadEvent::class,
                $config,
                $loadConfig->getCommandArgs(),
                $response
            );

            return $event->getResponse();
        })->otherwise(function ($reason) use ($loadConfig, $config) {
            return static::getExceptionHandler($loadConfig, $config)->handle($reason);
        });
    }

    private static function getCachedData(
        ResponseModelConfig $config,
        LoadConfig $loadConfig
    ): array {
        $client = $loadConfig->getClient();
        $commandArgs = $loadConfig->getCommandArgs();

        if ($client->canCache()) {
            $hash = CacheHash::getHashForResponseClassAndArgs(
                $config->getResponseModelClass(),
                $commandArgs,
                $client->getOutput()
            );

            if ($loadConfig->doClearCache()) {
                LogUtil::debug($client->getOutput(), 'Clearing cache for ' . $hash . '!');
                $client->getCache()->delete($hash);
            }

            /**
             * @var ResponseModelPreLoadFromCacheEvent $event
             */
            $event = $client->dispatchEvent(
                ResponseModelPreLoadFromCacheEvent::class,
                $config,
                $hash
            );

            //We allow our event to overwrite the hash to use.
            $hash = $event->getHash();

            if ($client->getCache()->contains($hash)) {
                $data = $client->getCache()->fetch($hash);

                /**
                 * @var ResponseModelPostLoadFromCacheEvent $event
                 */
                $event = $client->dispatchEvent(
                    ResponseModelPostLoadFromCacheEvent::class,
                    $config,
                    $hash,
                    $data
                );

                //We allow our event to have data that is modified.
                return [$hash, $event->getCachedData()];
            }

            //We went through the trouble of generating our hash, we'll send it back so we can use it
            //later but we won't return any cached data since we don't have it
            return [$hash, null];
        }

        //Couldn't cache anything
        return [null, null];
    }

    /**
     * Get the command representing this model's config and additional load config.
     *
     * @param ResponseModelConfig $config     the model's configuration
     * @param LoadConfig          $loadConfig the configuration to use during loading
     *
     * @return CommandInterface
     */
    private static function getLoadCommand(
        ResponseModelConfig $config,
        LoadConfig          $loadConfig
    ): CommandInterface {
        $client      = $loadConfig->getClient();
        $commandArgs = $loadConfig->getCommandArgs();

        /**
         * @var ResponseModelPreGetLoadCommandEvent $event
         */
        $event = $client->dispatchEvent(
            ResponseModelPreGetLoadCommandEvent::class,
            $config,
            $commandArgs
        );

        //The event could have modified the command arguments
        $commandArgs = $event->getCommandArgs();

        //We'll take our model's configured arguments and merge them with the final command arguments
        $finalArgs   = array_merge_recursive($config->getDefaultArgs(), $commandArgs);

        return $client->getCommand(
            $config->getCommand(),
            $finalArgs
        );
    }

    private static function getExecuteCommandPromise(
        ResponseModelConfig $config,
        LoadConfig          $loadConfig,
        CommandInterface    $command,
        string              $cacheHash = null
    ): PromiseInterface {
        $client = $loadConfig->getClient();

        return Promise::async(function () use ($config, $client, $command) {
            /**
             * @var ResponseModelPreExecuteCommandEvent $event
             */
            $event = $client->dispatchEvent(
                ResponseModelPreExecuteCommandEvent::class,
                $config,
                $command
            );

            LogUtil::debugLogCommandRequest($client, $event->getCommand());

            return $event->getCommand();

        })->then(function ($command) use ($client) {
            return $client->executeAsync($command)->wait();
        })->then(function ($response) use ($client, $config, $command, $cacheHash) {

            //Save our response in cache if we can. We specifically do not wait until after we dispatch the post
            //execute command event because we may not always have the same response data from the event.
            if($client->canCache() && !is_null($cacheHash)) {
                LogUtil::debug($client->getOutput(), 'Saving to cache (' . $cacheHash . ')!');
                $client->getCache()->save($cacheHash, $response);
            }

            /**
             * @var ResponseModelPostExecuteCommandEvent $event
             */
            $event = $client->dispatchEvent(
                ResponseModelPostExecuteCommandEvent::class,
                $config,
                $command,
                $response
            );

            LogUtil::logResponse(
                $client->getOutput(),
                $config->getResponseModelClass(),
                $event->getResponse()
            );

            return $event->getResponse();
        });
    }
}
