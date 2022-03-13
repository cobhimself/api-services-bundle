<?php
/*
 * This file is part of the cobhimself/api-services-bundle package.
 *
 * (c) Collin D. Brooks <collin.brooks@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cob\Bundle\ApiServicesBundle\Models\Subscribers;

use Cob\Bundle\ApiServicesBundle\Models\CommandLineOutputInterface;
use Cob\Bundle\ApiServicesBundle\Models\CommandLineStringHelpersInterface;
use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\CommandFulfilledEvent;
use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\PostAddDataFromParentEvent;
use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\PostAddModelToCollectionEvent;
use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\PostCountEvent;
use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\PostExecuteCommandsEvent;
use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\PostLoadEvent;
use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\PostLoadFromCacheEvent;
use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\PostRunAllPromisesEvent;
use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\PostRunPromiseInAllEvent;
use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\PreAddDataFromParentEvent;
use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\PreCountEvent;
use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\PreExecuteCommandsEvent;
use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\PreLoadEvent;
use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\PreLoadFromCacheEvent;
use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\PreRunAllPromisesEvent;
use Cob\Bundle\ApiServicesBundle\Models\Util\Promise;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Abstract class which causes all events relating to ResponseModelCollectionInstance
 * instances to be registered for listening.
 */
abstract class AbstractResponseModelCollectionSubscriber implements EventSubscriberInterface, CommandLineOutputInterface, CommandLineStringHelpersInterface
{
    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents(): array
    {
        return [
            PreLoadEvent::NAME => [
                'onPreLoad',
            ],
            PreCountEvent::NAME => [
                'onPreCount',
            ],
            PostCountEvent::NAME => [
                'onPostCount',
            ],
            PreLoadFromCacheEvent::NAME => [
                'onPreLoadFromCache',
            ],
            PostLoadFromCacheEvent::NAME => [
                'onPostLoadFromCache',
            ],
            PreExecuteCommandsEvent::NAME => [
                'onPreExecuteCommands',
            ],
            CommandFulfilledEvent::NAME => [
                'onCommandFulfilled',
            ],
            PostExecuteCommandsEvent::NAME => [
                'onPostExecuteCommands',
            ],
            PostLoadEvent::NAME => [
                'onPostLoad',
            ],
            PreAddDataFromParentEvent::NAME => [
                'onPreAddDataFromParent',
            ],
            PostAddDataFromParentEvent::NAME => [
                'onPostAddDataFromParent',
            ],
            PreRunAllPromisesEvent::NAME => [
                'onPreRunAllPromises',
            ],
            PostRunPromiseInAllEvent::NAME => [
                'onPostRunPromiseInAll',
            ],
            PostRunAllPromisesEvent::NAME => [
                'onPostRunAllPromises',
            ],
            PostAddModelToCollectionEvent::NAME => [
                'onPostAddModelToCollection',
            ],
        ];
    }

    /**
     * Run after a command has been fulfilled.
     *
     * @return null|mixed if null, no modification is made to the value
     *                    in the collection. Otherwise, we use the value
     *                    returned as the value for the command's response.
     */
    abstract public function onCommandFulfilled(
        CommandFulfilledEvent $event
    );

    /**
     * Run after a group of commands are executed
     */
    abstract public function onPostExecuteCommands(
        PostExecuteCommandsEvent $event
    );

    /**
     * Run after a model has been added to a collection.
     */
    abstract public function onPostAddModelToCollection(
        PostAddModelToCollectionEvent $event
    );

    /**
     * Run after the count information is retrieved for a collection.
     */
    abstract public function onPostCount(
        PostCountEvent $event
    );

    /**
     * Run after an individual promise within a group of promises is resolved.
     *
     * @see Promise::all()
     */
    abstract public function onPostRunPromiseInAll(
        PostRunPromiseInAllEvent $event
    );

    /**
     * Run after all promises within a collection are fulfilled.
     *
     * @see Promise::all()
     */
    abstract public function onPostRunAllPromises(
        PostRunAllPromisesEvent $event
    );

    /**
     * Run after the collection has been loaded.
     */
    abstract public function onPostLoad(
        PostLoadEvent $event
    );

    /**
     * Run after our responses have been retrieved from cache.
     *
     * @return null|array If returning null, no modification is made to the
     *                    responses array in the collection. Otherwise, the
     *                    returned array will replace the collection's responses
     *                    array.
     */
    abstract public function onPostLoadFromCache(
        PostLoadFromCacheEvent $event
    );

    /**
     * Run before data is added to a collection from a parent model.
     */
    abstract public function onPreAddDataFromParent(
        PreAddDataFromParentEvent $event
    );

    /**
     * Run after data is added to a collection from a parent model.
     */
    abstract public function onPostAddDataFromParent(
        PostAddDataFromParentEvent $event
    );

    /**
     * Run before the count information is retrieved for a collection.
     */
    abstract public function onPreCount(
        PreCountEvent $event
    );

    /**
     * Run before a chunked set of commands are run for the collection.
     *
     * @return null|array If null, no modification is made to the commands.
     *                    Otherwise, the returned array replaces the commands
     *                    to be executed.
     */
    abstract public function onPreExecuteCommands(
        PreExecuteCommandsEvent $event
    );

    /**
     * Run before a collection of promises is run.
     *
     * @see Promise::all()
     */
    abstract public function onPreRunAllPromises(
        PreRunAllPromisesEvent $event
    );

    /**
     * Run before any loading is done in the collection.
     */
    abstract public function onPreLoad(
        PreLoadEvent $event
    );

    /**
     * Run before a collection is loaded from cache.
     *
     * @param PreLoadFromCacheEvent $event
     */
    abstract public function onPreLoadFromCache(
        PreLoadFromCacheEvent $event
    );
}
