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
use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\ResponseModelPostExecuteCommandEvent;
use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\ResponseModelPostLoadEvent;
use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\ResponseModelPostLoadFromCacheEvent;
use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\ResponseModelPreExecuteCommandEvent;
use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\ResponseModelPreLoadEvent;
use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\ResponseModelPreLoadFromCacheEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Abstract class which causes all events relating to ResponseModelInstance
 * instances to be registered for listening.
 */
abstract class AbstractResponseModelSubscriber implements EventSubscriberInterface, CommandLineOutputInterface, CommandLineStringHelpersInterface
{
    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents(): array
    {
        return [
            ResponseModelPreLoadEvent::NAME => [
                'onPreLoadEvent',
            ],
            ResponseModelPostLoadEvent::NAME => [
                'onPostLoadEvent',
            ],
            ResponseModelPreExecuteCommandEvent::NAME => [
                'onPreExecuteCommandEvent',
            ],
            ResponseModelPreLoadFromCacheEvent::NAME => [
                'onPreLoadFromCacheEvent',
            ],
            ResponseModelPostLoadFromCacheEvent::NAME => [
                'onPostLoadFromCacheEvent',
            ],
            ResponseModelPostExecuteCommandEvent::NAME => [
                'onPostExecuteCommandEvent',
            ],
        ];
    }

    /**
     * Run before a response model is loaded.
     */
    abstract public function onPreLoadEvent(ResponseModelPreLoadEvent $event);

    /**
     * Run after a response model is loaded.
     */
    abstract public function onPostLoadEvent(ResponseModelPostLoadEvent $event);

    /**
     * Run before a command is run for a response model.
     */
    abstract public function onPreExecuteCommandEvent(
        ResponseModelPreExecuteCommandEvent $event
    );

    /**
     * Run before a response model is loaded from cache.
     */
    abstract public function onPreLoadFromCacheEvent(
        ResponseModelPreLoadFromCacheEvent $event
    );

    /**
     * Run after a response model is loaded from cache.
     */
    abstract public function onPostLoadFromCacheEvent(
        ResponseModelPostLoadFromCacheEvent $event
    );

    /**
     * Run after a command is executed on a response model.
     */
    abstract public function onPostExecuteCommandEvent(
        ResponseModelPostExecuteCommandEvent $event
    );
}
