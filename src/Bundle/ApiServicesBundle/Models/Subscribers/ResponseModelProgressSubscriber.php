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

use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\ResponseModelPostExecuteCommandEvent;
use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\ResponseModelPostLoadEvent;
use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\ResponseModelPostLoadFromCacheEvent;
use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\ResponseModelPreExecuteCommandEvent;
use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\ResponseModelPreLoadEvent;
use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\ResponseModelPreLoadFromCacheEvent;

/**
 * Reports the progress of loading a response model
 */
class ResponseModelProgressSubscriber extends AbstractResponseModelSubscriber
{
    use ProgressTrait;

    /**
     * @inheritDoc
     */
    public function onPreLoadEvent(ResponseModelPreLoadEvent $event)
    {
        $this->outputEvent(get_class($event->getModel()));
    }

    /**
     * @inheritDoc
     */
    public function onPostLoadEvent(ResponseModelPostLoadEvent $event)
    {
        $this->outputEvent(get_class($event->getModel()));
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
        $class = get_class($event->getModel());

        $this->outputEvent($class);
    }

    /**
     * @inheritDoc
     */
    public function onPostExecuteCommandEvent(
        ResponseModelPostExecuteCommandEvent $event
    ) {
        $class = get_class($event->getModel());

        $this->outputEvent($class);
    }

    /**
     * @inheritDoc
     */
    public function onPreLoadFromCacheEvent(
        ResponseModelPreLoadFromCacheEvent $event
    ) {
        $class = get_class($event->getModel());

        $this->outputEvent($class);
    }
}
