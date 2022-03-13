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

/**
 * Handles progress bar integration with ResponseModelCollectionInterface
 * events.
 */
class ResponseModelCollectionProgressSubscriber extends AbstractResponseModelCollectionSubscriber
{
    const INITIALIZING = 'Initializing:';
    const LOADING = 'Loading:';
    const FROM_CACHE = 'From cache:';

    use ProgressTrait;

    /**
     * @inheritDoc
     */
    public function onPreLoad(
        PreLoadEvent $event
    ) {
        $collection = $event->getResponseModelClass();
        $this->outputEvent($collection);

        //At this point, we're initializing a collection.
        $this->setInfo(self::INITIALIZING, $collection);

        //We don't know the size of the collection so just display the progress
        $this->getProgressBar()->start();
    }

    /**
     * @inheritDoc
     */
    public function onPreCount(
        PreCountEvent $event
    ) {
        $collection = $event->getResponseModelClass();

        $this->outputEvent($collection);
        $this->setContext($collection);
    }

    /**
     * @inheritDoc
     */
    public function onPostCount(
        PostCountEvent $event
    ) {
        $collection = $event->getResponseModelClass();
        $this->outputEvent($collection . ', ' . $event->getCount());
        $this->getProgressBar()->start($event->getCount());
    }

    /**
     * @inheritDoc
     */
    public function onPreExecuteCommands(
        PreExecuteCommandsEvent $event
    ) {
        $model      = $event->getResponseModelClass();
        $totalCommands = count($event->getCommands());

        $this->outputEvent($model . ', ' . $totalCommands);

        //We've got a list of commands to execute...
        $this->allowAdvance();
        $this->setInfo(self::LOADING, $model);
        $this->getProgressBar()->start($totalCommands);
    }

    /**
     * @inheritDoc
     */
    public function onCommandFulfilled(
        CommandFulfilledEvent $event
    ) {
        $collection = $event->getResponseModelClass();
        $commands = $event->getCommands();
        $currentCommandIndex = $event->getIndex();
        $totalCommandsToLoad = count($commands);

        $this->outputEvent(sprintf(
            "%s, %s/%s",
            $collection,
            $currentCommandIndex,
            $totalCommandsToLoad
        ));

        $this->setContext($collection);
        $this->advance();
    }

    public function onPostExecuteCommands(PostExecuteCommandsEvent $event)
    {
        $model      = $event->getResponseModelClass();

        $this->outputEvent($model . ', ' . count($event->getCommands()));
        $this->resetProgressBar();
    }

    /**
     * @inheritDoc
     */
    public function onPostAddModelToCollection(
        PostAddModelToCollectionEvent $event
    ) {
        $this->outputEvent($event->getModel());
    }

    /**
     * @inheritDoc
     */
    public function onPostLoad(
        PostLoadEvent $event
    ) {
        $this->outputEvent($event->getResponseModelClass());

        //Our collection is done loading. We're finished.
        $this->resetProgressBar();
    }

    /**
     * @inheritDoc
     */
    public function onPreLoadFromCache(PreLoadFromCacheEvent $event)
    {
        $model = $event->getResponseModelClass();
        $this->outputEvent($model);
        $this->allowAdvance();
        $this->setInfo(self::FROM_CACHE, $model);
        $this->getProgressBar()->start(0);
    }

    /**
     * @inheritDoc
     */
    public function onPostLoadFromCache(
        PostLoadFromCacheEvent $event
    ) {
        $this->outputEvent($event->getResponseModelClass());

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
        $this->setInfo(self::LOADING, $event->getContext());
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
        $this->setInfo(self::LOADING, $event->getValue());
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
            $event->getResponseModelClass()
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
            $event->getResponseModelClass()
        ));
    }
}
