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

use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\CommandFulfilledEvent;
use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\PostAddDataFromParentEvent;
use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\PostAddFromResponsesEvent;
use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\PostAddModelToCollectionEvent;
use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\PostCountEvent;
use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\PostExecuteCommandsEvent;
use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\PostLoadEvent;
use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\PostLoadFromCacheEvent;
use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\PostRunAllPromisesEvent;
use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\PostRunPromiseInAllEvent;
use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\PreAddDataFromParentEvent;
use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\PreAddFromResponsesEvent;
use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\PreCountEvent;
use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\PreExecuteCommandsEvent;
use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\PreLoadEvent;
use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\PreLoadFromCacheEvent;
use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\PreRunAllPromisesEvent;

/**
 * Handles progress bar integration with ResponseModelCollectionInterface
 * events.
 */
class ResponseModelCollectionProgressSubscriber extends AbstractResponseModelCollectionSubscriber
{
    use ProgressTrait;

    /**
     * @inheritDoc
     */
    public function onPreLoad(
        PreLoadEvent $event
    ) {
        $collection = $event->getCollection();
        $this->outputEvent(get_class($collection) . ', ' . $collection->getSize());

        //At this point, we're initializing a collection.
        $this->setInfo('Initializing:', $collection);

        //We don't know the size of the collection so just display the progress
        $this->getProgressBar()->start();
    }

    /**
     * @inheritDoc
     */
    public function onPreCount(
        PreCountEvent $event
    ) {
        $collection = $event->getCollection();

        $this->outputEvent($collection);
        $this->setContext($collection);
    }

    /**
     * @inheritDoc
     */
    public function onPostCount(
        PostCountEvent $event
    ) {
        $collection = $event->getCollection();
        $this->outputEvent(get_class($collection) . ', ' . $collection->getSize());
        $this->getProgressBar()->start($collection->getSize());
    }

    /**
     * @inheritDoc
     */
    public function onPreExecuteCommands(
        PreExecuteCommandsEvent $event
    ) {
        $collection = $event->getCollection();
        $model      = get_class($collection);

        $this->outputEvent($model . ', ' . $collection->getSize());

        //We've got a list of commands to execute...
        $this->allowAdvance();
        $this->setInfo('Loading:', $collection);
        $this->getProgressBar()->start($collection->getSize());
    }

    /**
     * @inheritDoc
     */
    public function onCommandFulfilled(
        CommandFulfilledEvent $event
    ) {
        $collection = $event->getCollection();
        $commands = $event->getCommands();
        $currentCommandIndex = $event->getIndex();
        $totalCommandsToLoad = count($commands);
        $finalCollectionSize = $collection->getSize();

        $this->outputEvent(sprintf(
            "%s, %s/%s",
            get_class($collection),
            $currentCommandIndex,
            $totalCommandsToLoad
        ));

        $this->setContext($collection);
        $this->advance($finalCollectionSize / $totalCommandsToLoad);
    }

    public function onPostExecuteCommands(PostExecuteCommandsEvent $event)
    {
        $collection = $event->getCollection();
        $model      = get_class($collection);

        $this->outputEvent($model . ', ' . $collection->getSize());
        $this->resetProgressBar();
    }

    public function onPreAddFromResponses(PreAddFromResponsesEvent $event)
    {
        $this->outputEvent($event->getCollection());
    }

    /**
     * @inheritDoc
     */
    public function onPostAddModelToCollection(
        PostAddModelToCollectionEvent $event
    ) {
        $this->outputEvent($event->getModel());
    }

    public function onPostAddFromResponses(PostAddFromResponsesEvent $event)
    {
        $this->outputEvent($event->getCollection());
    }

    /**
     * @inheritDoc
     */
    public function onPostLoad(
        PostLoadEvent $event
    ) {
        $this->outputEvent($event->getCollection());

        //Our collection is done loading. We're finished.
        $this->resetProgressBar();
    }

    /**
     * @inheritDoc
     */
    public function onPreLoadFromCache(PreLoadFromCacheEvent $event)
    {
        $this->outputEvent($event->getCollection());
        $this->allowAdvance();
        $this->setInfo('From cache:', $event->getCollection());
        $this->getProgressBar()->start(0);
    }

    /**
     * @inheritDoc
     */
    public function onPostLoadFromCache(
        PostLoadFromCacheEvent $event
    ) {
        $this->outputEvent($event->getCollection());

        //Our collection has been loaded from cache. Good as done.
        $this->resetProgressBar();
    }

    /**
     * @inheritDoc
     */
    public function onPreRunAllPromises(
        PreRunAllPromisesEvent $event
    ) {
        $this->outputEvent($event->getNumItems());

        //We are about to generate data from a list of promises. Start the
        //progress bar over.
        $this->allowAdvance();
        $this->setInfo('Loading:', $event->getContext());
        $this->getProgressBar()->start($event->getNumItems());
    }

    public function onPostRunPromiseInAll(
        PostRunPromiseInAllEvent $event
    ) {
        $value = $event->getValue();
        if (is_object($value)) {
            $value = get_class($value);
        } elseif (!is_string($value)) {
            $value = '';
        }

        $this->outputEvent(sprintf(
            '%s, %s/%s',
            $value,
            $event->getIndex(),
            $event->getCollectionSize()
        ));

        //In this instance, a single promise within the collection has been
        //fulfilled, we'll advance.
        $this->allowAdvance();
        $this->setInfo('Loading:', $event->getValue());
        $this->advance();
    }

    /**
     * @inheritDoc
     */
    public function onPostRunAllPromises(
        PostRunAllPromisesEvent $event
    ) {
        $this->outputEvent('');

        //Our entire set of promises has run. We'll finish our progress here.
        $this->resetProgressBar();
    }

    /**
     * @inheritDoc
     */
    public function onPreAddDataFromParent(PreAddDataFromParentEvent $event)
    {
        $this->outputEvent(sprintf(
            '%s:%s',
            get_class($event->getParentModel()),
            get_class($event->getCollection())
        ));
    }

    /**
     * @inheritDoc
     */
    public function onPostAddDataFromParent(PostAddDataFromParentEvent $event)
    {
        $this->outputEvent(sprintf(
            '%s:%s',
            get_class($event->getParentModel()),
            get_class($event->getCollection())
        ));
    }
}
