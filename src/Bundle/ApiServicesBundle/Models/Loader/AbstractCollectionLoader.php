<?php

namespace Cob\Bundle\ApiServicesBundle\Models\Loader;

use Cob\Bundle\ApiServicesBundle\Exceptions\ResponseModelCollectionException;
use Cob\Bundle\ApiServicesBundle\Models\Count;
use Cob\Bundle\ApiServicesBundle\Models\DotData;
use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\CommandFulfilledEvent;
use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\PostExecuteCommandEvent;
use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\PostExecuteCommandsEvent;
use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\PostLoadEvent;
use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\PostLoadFromCacheEvent;
use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\PreExecuteCommandEvent;
use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\PreExecuteCommandsEvent;
use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\PreGetLoadCommandEvent;
use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\PreLoadEvent;
use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\PreLoadFromCacheEvent;
use Cob\Bundle\ApiServicesBundle\Models\Loader\Config\CollectionLoadConfig;
use Cob\Bundle\ApiServicesBundle\Models\ResponseModelCollection;
use Cob\Bundle\ApiServicesBundle\Models\ResponseModelCollectionConfig;
use Cob\Bundle\ApiServicesBundle\Models\ServiceClientInterface;
use Cob\Bundle\ApiServicesBundle\Models\Util\CacheHash;
use Cob\Bundle\ApiServicesBundle\Models\Util\ClassUtil;
use Cob\Bundle\ApiServicesBundle\Models\Util\Promise;
use GuzzleHttp\Command\CommandInterface;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Promise\PromiseInterface;

/**
 * Abstract loader containing the bulk of the response model collection methods.
 */
abstract class AbstractCollectionLoader implements CollectionLoaderInterface
{
    /**
     * Quickly obtain a response collection class after confirming it represents a {@link ResponseModelCollection}.
     *
     * @param ResponseModelCollectionConfig $config the response model config we want to get a new model for
     * @param CollectionLoadConfig $loadConfig
     * @param LoadState $loadState the load state we desire the response model to be
     *                                                 initialized with
     * @param PromiseInterface $promise the Promise the response model will use to obtain its data
     *
     * @return ResponseModelCollection
     */
    protected static function getNewResponseCollectionClass(
        ResponseModelCollectionConfig $config,
        CollectionLoadConfig $loadConfig,
        LoadState $loadState,
        PromiseInterface $promise
    ): ResponseModelCollection {
        $responseClass = $config->getResponseModelClass();

        ClassUtil::confirmValidResponseModelCollection($responseClass);

        return new $responseClass(
            $loadConfig->getClient(),
            $loadState,
            $promise,
            $loadConfig->getParent()
        );
    }

    /**
     * Get the {@link PromiseInterface} needed to load data for a given {@link CollectionLoadConfig}.
     *
     * @param ResponseModelCollectionConfig $config the response model config to use when loading
     * @param CollectionLoadConfig $loadConfig the load-time specific configuration
     *
     * @return PromiseInterface
     */
    protected static function getLoadPromise(
        ResponseModelCollectionConfig $config,
        CollectionLoadConfig $loadConfig
    ): PromiseInterface
    {
        ClassUtil::confirmValidResponseModelCollection($config->getResponseModelClass());

        //Since we allow the PreLoadEvent to modify the load configuration, provide a clone
        $loadConfig = clone $loadConfig;

        return Promise::async(function () use ($config, &$loadConfig) {
            /**
             * @var PreLoadEvent $event
             */
            $event = $loadConfig->getClient()->dispatchEvent(
                PreLoadEvent::class,
                $config,
                $loadConfig
            );

            //Allow our event to update the command arguments
            $loadConfig = $event->getLoadConfig();

        })->then(function () use ($config, $loadConfig) {
            //Can we load from cache?
            list($hash, $cache) = static::getCachedData($config, $loadConfig);

            if (!is_null($cache)) {
                return new FulfilledPromise($cache);
            } else if ($config->hasCountCommand()) {
                return static::getLoadPromiseUsingCount($config, $loadConfig, $hash);
            } else {
                $command = static::getLoadCommand($config, $loadConfig);

                return static::getExecuteCommandPromise($config, $loadConfig, $command, $hash)->wait();
            }
        })->then(function ($response) use ($config, $loadConfig) {
            /**
             * @var PostLoadEvent $event
             */
            $event = $loadConfig->getClient()->dispatchEvent(
                PostLoadEvent::class,
                $config,
                $loadConfig,
                $response
            );

            return $event->getResponse();
        })->otherwise(function ($reason) use ($loadConfig) {
            //If there is a problem, let our error handler take care of it
            return $loadConfig->getExceptionHandler()->handle($reason);
        });
    }

    /**
     * Get the cached data for the response model based on a previous command run by the response model matching the
     * same load-time configuration.
     *
     * @param ResponseModelCollectionConfig $modelConfig the response model configuration
     * @param CollectionLoadConfig          $loadConfig  the load-time configuration
     *
     * @return array the cached data
     */
    private static function getCachedData(
        ResponseModelCollectionConfig $modelConfig,
        CollectionLoadConfig          $loadConfig
    ): array {
        $hash = CacheHash::getHashForResponseCollectionClassAndArgs(
            $modelConfig->getResponseModelClass(),
            $loadConfig->getCommandArgs()
        );

        $client = $loadConfig->getClient();

        if ($client->canCache()) {

            /**
             * @var PreLoadFromCacheEvent $event
             */
            $event = $client->dispatchEvent(
                PreLoadFromCacheEvent::class,
                $modelConfig,
                $hash
            );

            //We allow our event to overwrite the hash to use.
            $hash = $event->getHash();

            //Do we even have the hash in our cache?
            if ($client->getCache()->contains($hash)) {
                $data = $client->getCache()->fetch($hash);

                /**
                 * @var PostLoadFromCacheEvent $event
                 */
                $event = $client->dispatchEvent(
                    PostLoadFromCacheEvent::class,
                    $modelConfig,
                    $hash,
                    $data
                );

                //We allow our event to have data that is modified.
                return [$hash, $event->getResponseData()];
            }
        }

        return [$hash, null];
    }

    /**
     * Get the command our service client will used to load the data for the given response model config and
     * load-time config.
     *
     * @param ResponseModelCollectionConfig $modelConfig the model's configuration
     * @param CollectionLoadConfig          $loadConfig  the load-time configuration
     *
     * @return CommandInterface the command to run
     */
    private static function getLoadCommand(
        ResponseModelCollectionConfig $modelConfig,
        CollectionLoadConfig          $loadConfig
    ): CommandInterface {
        /**
         * @var PreGetLoadCommandEvent $event
         */
        $event = $loadConfig->getClient()->dispatchEvent(
            PreGetLoadCommandEvent::class,
            $modelConfig,
            $loadConfig
        );

        //We allow our load config to be modified...
        $loadConfig = $event->getLoadConfig();

        //The final arguments for our command is made up of the arguments defined within the response model's config
        //as well as the load-time command arguments. This allows the default arguments the response model's config
        //may have set to be overridden or additional arguments to be provided.
        $finalArgs = array_merge_recursive(
            $modelConfig->getDefaultArgs(),
            $loadConfig->getCommandArgs()
        );

        return $loadConfig->getClient()->getCommand(
            $modelConfig->getCommand(),
            $finalArgs
        );
    }

    /**
     * Get the promise responsible for running the load command.
     *
     * @param ResponseModelCollectionConfig $modelConfig the response model's configuration
     * @param CollectionLoadConfig          $loadConfig  the load-time configuration
     * @param CommandInterface              $command     the command to run to obtain data for the model
     * @param string|null                   $cacheHash   the hash used to fetch/save data for the model
     *
     * @return PromiseInterface
     */
    private static function getExecuteCommandPromise(
        ResponseModelCollectionConfig $modelConfig,
        CollectionLoadConfig          $loadConfig,
        CommandInterface              $command,
        string                        $cacheHash = null
    ): PromiseInterface {
        $client = $loadConfig->getClient();

        return Promise::async(function () use ($modelConfig, $client, $command) {
            /**
             * @var PreExecuteCommandEvent $event
             */
            $event = $client->dispatchEvent(
                PreExecuteCommandEvent::class,
                $modelConfig,
                $command
            );

            return $event->getCommand();
        })->then(function ($command) use ($client) {
            //We do not run this asynchronously because the data is required to move forward
            return $client->execute($command);
        })->then(function ($response) use ($client, $modelConfig, $command, $cacheHash) {

            //Save our response in cache if we can. We specifically do not wait until after we dispatch the post
            //execute command event because we may not always get the same response data from the event.
            static::attemptSaveCache($client, $cacheHash, $response);

            /**
             * @var PostExecuteCommandEvent $event
             */
            $event = $client->dispatchEvent(
                PostExecuteCommandEvent::class,
                $modelConfig,
                $command,
                $response
            );

            return $event->getResponse();
        });
    }

    /**
     * Get the promise used to load our collection in chunks.
     *
     * @param ResponseModelCollectionConfig $modelConfig the model's configuration
     * @param CollectionLoadConfig          $loadConfig  the load-time configuration
     * @param string                        $hash        the hash for the response model to fetch/save cache
     *
     * @return PromiseInterface the Promise needed to load the response model
     */
    private static function getLoadPromiseUsingCount(
        ResponseModelCollectionConfig $modelConfig,
        CollectionLoadConfig          $loadConfig,
        string                        $hash
    ): PromiseInterface {
        $client      = $loadConfig->getClient();
        $commandArgs = $loadConfig->getCommandArgs();

        return Promise::async(function () use ($modelConfig, $client) {
            //Our first step is to obtain count information so we can split our requests into chunks.
            return Count::get($modelConfig, $client);
        })->then(function ($countResponse) use ($client, $modelConfig, $commandArgs, $hash) {
            $commands = $client->getChunkedCommands(
                $modelConfig->getCommand(),
                $commandArgs,
                $countResponse,
                $modelConfig->getBuildCountArgsCallback(),
                $modelConfig->getChunkCommandMaxResults()
            );

            return static::executeCommandCollection($client, $modelConfig, $commands, $hash);
        });
    }

    /**
     * Execute the command collection load process.
     *
     * @param ServiceClientInterface        $client      the service client doing the loading
     * @param ResponseModelCollectionConfig $modelConfig the response model's configuration
     * @param array                         $commands    the commands we will be executing
     * @param string                        $hash        the hash used to fetch/save cache
     *
     * @return PromiseInterface
     */
    protected static function executeCommandCollection(
        ServiceClientInterface        $client,
        ResponseModelCollectionConfig $modelConfig,
        array                         $commands,
        string                        $hash
    ): PromiseInterface {
        return Promise::async(function () use ($client, $modelConfig, $commands, $hash) {
            //Allow others to modify the commands before execution.
            /* @var PreExecuteCommandsEvent $event */
            $event = $client->dispatchEvent(
                PreExecuteCommandsEvent::class,
                $modelConfig,
                $commands
            );

            //Use the command from the dispatched event if it's not null
            $commands = $event->getCommands() ?? $commands;

            $allResponse = [];

            static::executeAllCommands(
                $modelConfig,
                $client,
                $commands,
                $allResponse
            )->wait();

            /**
             * @var PostExecuteCommandsEvent $event
             */
            $event = $client->dispatchEvent(
                PostExecuteCommandsEvent::class,
                $modelConfig,
                $commands,
                $allResponse
            );

            $allResponse = $event->getResponse();

            static::attemptSaveCache($client, $hash, $allResponse);

            return new FulfilledPromise($allResponse);
        });
    }

    /**
     * Have the service client execute each of the commands in the list of commands given.
     *
     * @param ResponseModelCollectionConfig $modelConfig the response model's configuration
     * @param ServiceClientInterface        $client      the service client loading the data
     * @param array                         $commands    the list of commands we're running
     * @param array                         $response    the final response array passed in by reference.
     *
     * @return PromiseInterface
     */
    private static function executeAllCommands(
        ResponseModelCollectionConfig $modelConfig,
        ServiceClientInterface        $client,
        array                         $commands,
        array                         &$response
    ): PromiseInterface {
        return $client->executeAllAsync($commands, [
            //If our responses were received correctly...
            'fulfilled' => function (
                $value,
                $index,
                PromiseInterface $aggregate
            ) use ($modelConfig, $client, $commands, &$response) {
                /** @var CommandFulfilledEvent $event */
                $event = $client->dispatchEvent(
                    CommandFulfilledEvent::class,
                    $modelConfig,
                    $commands,
                    $index,
                    $value,
                    $aggregate
                );

                //Use the command from the dispatched event in case it
                //was modified.
                $value = new DotData($event->getValue() ?? []);
                $path = $modelConfig->getCollectionPath();

                //Combine this response with all the other responses we've received.
                $response[$path] = array_merge(
                    $response[$path] ?? [],
                    $value->dot($path)
                );
            },
            'rejected'  => function ($reason) {
                throw new ResponseModelCollectionException(
                    'There was an issue when running all of the commands.', $reason
                );
            },
        ]);
    }

    /**
     * Attempt to save response data to cache.
     *
     * @param ServiceClientInterface $client    the service client whose cache provider we should use
     * @param string                 $cacheHash the hash used to fetch/save our response data
     * @param mixed                  $data      the data to save to cache
     */
    private static function attemptSaveCache(
        ServiceClientInterface $client,
        string $cacheHash,
        $data
    ) {
        if ($client->canCache()) {
            $client->getCache()->save($cacheHash, $data);
        }
    }
}
