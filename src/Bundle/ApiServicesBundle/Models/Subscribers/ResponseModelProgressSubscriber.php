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

use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\ResponseModelPostExecuteCommandEvent;
use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\ResponseModelPostLoadEvent;
use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\ResponseModelPostLoadFromCacheEvent;
use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\ResponseModelPreExecuteCommandEvent;
use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\ResponseModelPreLoadEvent;
use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\ResponseModelPreLoadFromCacheEvent;

/**
 * Reports the progress of loading a response model
 */
class ResponseModelProgressSubscriber extends AbstractResponseModelSubscriber
{
    //use ProgressTrait;

    /**
     * @inheritDoc
     */
    public function onPreLoadEvent(ResponseModelPreLoadEvent $event)
    {
        $this->outputEvent($event->getConfig()->getResponseModelClass());
    }

    /**
     * @inheritDoc
     */
    public function onPostLoadEvent(ResponseModelPostLoadEvent $event)
    {
        $this->outputEvent($event->getConfig()->getResponseModelClass());
    }

    /**
     * @inheritDoc
     */
    public function onPreExecuteCommandEvent(
        ResponseModelPreExecuteCommandEvent $event
    ) {
        $command = $event->getCommand();
        $args    = implode(', ', $command->toArray());

        $this->outputEvent(sprintf(
            '%s, %s: %s',
            get_class($command),
            $command->getName(),
            $args
        ));
    }

    /**
     * @inheritDoc
     */
    public function onPostLoadFromCacheEvent(
        ResponseModelPostLoadFromCacheEvent $event
    ) {
        $this->outputEvent($event->getConfig()->getResponseModelClass());
    }

    /**
     * @inheritDoc
     */
    public function onPostExecuteCommandEvent(
        ResponseModelPostExecuteCommandEvent $event
    ) {
        $this->outputEvent($event->getConfig()->getResponseModelClass());
    }

    /**
     * @inheritDoc
     */
    public function onPreLoadFromCacheEvent(
        ResponseModelPreLoadFromCacheEvent $event
    ) {
        $this->outputEvent($event->getConfig()->getResponseModelClass());
    }
}
