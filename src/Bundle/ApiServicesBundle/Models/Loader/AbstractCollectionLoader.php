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
use Cob\Bundle\ApiServicesBundle\Models\ExceptionHandlers\ExceptionHandlerInterface;
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
     * @param CollectionLoadConfig $loadConfig
     * @return PromiseInterface
     */
    protected static function getLoadPromise(
        ResponseModelCollectionConfig $config,
        CollectionLoadConfig $loadConfig
    ): PromiseInterface
    {
        $responseModelClass = $config->getResponseModelClass();

        ClassUtil::confirmValidResponseModelCollection($responseModelClass);

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
        });
    }

    private static function getCachedData(
        ResponseModelCollectionConfig $config,
        CollectionLoadConfig $loadConfig
    ): array {
        $hash = CacheHash::getHashForResponseCollectionClassAndArgs(
            $config->getResponseModelClass(),
            $loadConfig->getCommandArgs()
        );

        $client = $loadConfig->getClient();

        if ($client->canCache()) {

            /**
             * @var PreLoadFromCacheEvent $event
             */
            $event = $client->dispatchEvent(
                PreLoadFromCacheEvent::class,
                $config,
                $hash
            );

            //We allow our event to overwrite the hash to use.
            $hash = $event->getHash();

            $data = $client->getCache()->fetch($hash);

            /**
             * @var PostLoadFromCacheEvent $event
             */
            $event = $client->dispatchEvent(
                PostLoadFromCacheEvent::class,
                $config,
                $hash,
                $data
            );

            //We allow our event to have data that is modified.
            return [$hash, $event->getResponseData()];
        }

        return [$hash, null];
    }

    private static function getLoadCommand(
        ResponseModelCollectionConfig $config,
        CollectionLoadConfig $loadConfig
    ): CommandInterface {
        /**
         * @var PreGetLoadCommandEvent $event
         */
        $event = $loadConfig->getClient()->dispatchEvent(
            PreGetLoadCommandEvent::class,
            $config,
            $loadConfig
        );

        $loadConfig = $event->getLoadConfig();

        $finalArgs = array_merge_recursive($config->getDefaultArgs(), $loadConfig->getCommandArgs());

        return $loadConfig->getClient()->getCommand(
            $config->getCommand(),
            $finalArgs
        );
    }

    private static function getExecuteCommandPromise(
        ResponseModelCollectionConfig $config,
        CollectionLoadConfig $loadConfig,
        CommandInterface $command,
        string $cacheHash = null
    ): PromiseInterface {
        $client = $loadConfig->getClient();

        return Promise::async(function () use ($config, $client, $command) {
            /**
             * @var PreExecuteCommandEvent $event
             */
            $event = $client->dispatchEvent(
                PreExecuteCommandEvent::class,
                $config,
                $command
            );

            return $event->getCommand();

        })->then(function ($command) use ($client) {
            return $client->executeAsync($command)->wait();
        })->then(function ($response) use ($client, $config, $command, $cacheHash) {

            //Save our response in cache if we can. We specifically do not wait until after we dispatch the post
            //execute command event because we may not always have the same response data from the event.
            static::attemptSaveCache($client, $cacheHash, $response);

            /**
             * @var PostExecuteCommandEvent $event
             */
            $event = $client->dispatchEvent(
                PostExecuteCommandEvent::class,
                $config,
                $command,
                $response
            );

            return $event->getResponse();
        });
    }

    private static function getLoadPromiseUsingCount(
        ResponseModelCollectionConfig $config,
        CollectionLoadConfig $loadConfig,
        string $hash
    ): PromiseInterface {
        $client      = $loadConfig->getClient();
        $commandArgs = $loadConfig->getCommandArgs();

        return Promise::async(function () use ($config, $client) {
            return Count::get($config, $client);
        })->then(function ($countResponse) use ($client, $config, $commandArgs, $hash) {
            $commands = $client->getChunkedCommands(
                $config->getCommand(),
                $commandArgs,
                $countResponse,
                $config->getBuildCountArgsCallback(),
                $config->getChunkCommandMaxResults()
            );

            return static::executeCommandCollection($client, $config, $commands, $hash);
        });
    }

    protected static function executeCommandCollection(
        ServiceClientInterface $client,
        ResponseModelCollectionConfig $config,
        array $commands,
        string $hash,
        ExceptionHandlerInterface $handler = null
    ): PromiseInterface {
        return Promise::async(function () use ($client, $config, $commands, $hash, $handler) {
            //Allow others to modify the commands before execution.
            /* @var PreExecuteCommandsEvent $event */
            $event = $client->dispatchEvent(
                PreExecuteCommandsEvent::class,
                $config,
                $commands
            );

            //Use the command from the dispatched event if it's not null
            $commands = $event->getCommands() ?? $commands;

            $allResponse = [];

            static::executeAllCommands($config, $client, $commands, $allResponse, $handler)->wait();

            /**
             * @var PostExecuteCommandsEvent $event
             */
            $event = $client->dispatchEvent(
                PostExecuteCommandsEvent::class,
                $config,
                $commands,
                $allResponse
            );

            $allResponse = $event->getResponse();

            static::attemptSaveCache($client, $hash, $allResponse);

            return new FulfilledPromise($allResponse);
        })->otherwise(function ($reason) {
            throw new ResponseModelCollectionException('Could not load data from command collection!', $reason);
        });
    }

    private static function executeAllCommands(
        ResponseModelCollectionConfig $config,
        ServiceClientInterface $client,
        array $commands,
        array &$response,
        ExceptionHandlerInterface $handler = null
    ): PromiseInterface {
        return $client->executeAllAsync($commands, [
            //If our responses were received correctly...
            'fulfilled' => function (
                $value,
                $index,
                PromiseInterface $aggregate
            ) use ($config, $client, $commands, &$response) {
                /** @var CommandFulfilledEvent $event */
                $event = $client->dispatchEvent(
                    CommandFulfilledEvent::class,
                    $config,
                    $commands,
                    $index,
                    $value,
                    $aggregate
                );

                //Use the command from the dispatched event in case it
                //was modified.
                $value = new DotData($event->getValue() ?? []);
                $path = $config->getCollectionPath();
                $response[$path] = array_merge(
                    $response[$path] ?? [],
                    $value->dot($path)
                );
            },
            'rejected'  => function ($reason) use ($handler) {
                $handler  = $handler ?? $this->getDefaultExceptionHandler();
                $response = $handler->handle($reason);
            },
        ]);
    }

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