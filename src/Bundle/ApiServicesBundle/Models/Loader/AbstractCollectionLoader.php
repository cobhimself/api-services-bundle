<?php

namespace Cob\Bundle\ApiServicesBundle\Models\Loader;

use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\PostLoadEvent;
use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\PostLoadFromCacheEvent;
use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\PreLoadEvent;
use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\PreLoadFromCacheEvent;
use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\ResponseModelPostExecuteCommandEvent;
use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\ResponseModelPostLoadEvent;
use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\ResponseModelPostLoadFromCacheEvent;
use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\ResponseModelPreExecuteCommandEvent;
use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\ResponseModelPreGetLoadCommandEvent;
use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\ResponseModelPreLoadFromCacheEvent;
use Cob\Bundle\ApiServicesBundle\Models\Loader\State\LoadState;
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
     * @param ResponseModelCollectionConfig $config    the response model config we want to get a new model for
     * @param ServiceClientInterface        $client    the service client used to load data
     * @param LoadState                     $loadState the load state we desire the response model to be
     *                                                 initialized with
     * @param PromiseInterface              $promise   the Promise the response model will use to obtain its data
     *
     * @return ResponseModelCollection
     */
    protected static function getNewResponseCollectionClass(
        ResponseModelCollectionConfig $config,
        ServiceClientInterface $client,
        LoadState $loadState,
        PromiseInterface $promise
    ): ResponseModelCollection {
        $responseClass = $config->getResponseModelClass();

        ClassUtil::confirmValidResponseModelCollection($responseClass);

        return new $responseClass($client, $loadState, $promise);
    }

    /**
     * Get the {@link PromiseInterface} needed to load data for a given {@link LoadConfiguration}.
     *
     * @param ResponseModelCollectionConfig    $config      the response model config to use when loading
     * @param ServiceClientInterface $client      the service client used to load data
     * @param array                  $commandArgs the command arguments to use when loading
     *
     * @return PromiseInterface
     */
    protected static function getLoadPromise(
        ResponseModelCollectionConfig $config,
        ServiceClientInterface $client,
        array $commandArgs = []
    ): PromiseInterface
    {
        $responseModelClass = $config->getResponseModelClass();

        ClassUtil::confirmValidResponseModelCollection($responseModelClass);

        return Promise::async(function () use ($config, $client, $commandArgs) {
            /**
             * @var PreLoadEvent $event
             */
            $client->dispatchEvent(
                PreLoadEvent::class,
                $config,
                $commandArgs
            );
        })->then(function () use ($config, $client, $commandArgs) {
            //Can we load from cache?
            list($hash, $cache) = static::getCachedData($config, $client, $commandArgs);

            if (!is_null($cache)) {
                return new FulfilledPromise($cache);
            } else if ($config->hasCountCommand()) {
                return static::getLoadPromiseUsingCount($config, $client, $commandArgs);
            } else {
                $command = static::getLoadCommand($config, $client, $commandArgs);

                return static::getExecuteCommandPromise($config, $client, $command, $hash)->wait();
            }
        })->then(function ($response) use ($config, $client, $commandArgs) {
            /**
             * @var ResponseModelPostLoadEvent $event
             */
            $event = $client->dispatchEvent(
                PostLoadEvent::class,
                $config,
                $commandArgs,
                $response
            );

            return $event->getResponse();
        });
    }

    private static function getCachedData(
        ResponseModelCollectionConfig $config,
        ServiceClientInterface $client,
        array $commandArgs
    ): array {
        if ($client->canCache()) {
            $hash = CacheHash::getHashForResponseCollectionClassAndArgs(
                $config->getResponseModelClass(),
                $commandArgs
            );

            /**
             * @var ResponseModelPreLoadFromCacheEvent $event
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
             * @var ResponseModelPostLoadFromCacheEvent $event
             */
            $event = $client->dispatchEvent(
                PostLoadFromCacheEvent::class,
                $config,
                $hash,
                $data
            );

            //We allow our event to have data that is modified.
            return [$hash, $event->getCachedData()];
        }

        return [null, null];
    }

    private static function getLoadCommand(
        ResponseModelCollectionConfig $config,
        ServiceClientInterface $client,
        array $commandArgs
    ): CommandInterface {
        /**
         * @var ResponseModelPreGetLoadCommandEvent $event
         */
        $event = $client->dispatchEvent(
            ResponseModelPreGetLoadCommandEvent::class,
            $config,
            $commandArgs
        );

        $commandArgs = $event->getCommandArgs();

        $finalArgs = array_merge_recursive($config->getDefaultArgs(), $commandArgs);

        return $client->getCommand(
            $config->getCommand(),
            $finalArgs
        );
    }

    private static function getExecuteCommandPromise(
        ResponseModelCollectionConfig $config,
        ServiceClientInterface $client,
        CommandInterface $command,
        string $cacheHash = null
    ): PromiseInterface {
        return Promise::async(function () use ($config, $client, $command) {
            /**
             * @var ResponseModelPreExecuteCommandEvent $event
             */
            $event = $client->dispatchEvent(
                ResponseModelPreExecuteCommandEvent::class,
                $config,
                $command
            );

            return $event->getCommand();

        })->then(function ($command) use ($client) {
            return $client->executeAsync($command)->wait();
        })->then(function ($response) use ($client, $config, $command, $cacheHash) {

            //Save our response in cache if we can. We specifically do not wait until after we dispatch the post
            //execute command event because we may not always have the same response data from the event.
            if($client->canCache() && !is_null($cacheHash)) {
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

            return $event->getResponse();
        });
    }

    private static function getLoadPromiseUsingCount(
        ResponseModelCollectionConfig $config,
        ServiceClientInterface $client,
        array $commandArgs
    ): PromiseInterface {
        return Promise::async(function () use ($config, $client, $commandArgs) {
            $finalArgs = array_merge_recursive($config->getDefaultArgs(), $commandArgs);
            $command = $client->getCommand($config->getCountCommand(), $finalArgs);
            return $client->execute($command);
        })->then(function ($countResponse) {

        });
    }
}