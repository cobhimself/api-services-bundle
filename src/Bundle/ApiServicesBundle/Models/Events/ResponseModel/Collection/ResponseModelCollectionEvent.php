<?php
/*
 * This file is part of the cobhimself/api-services-bundle package.
 *
 * (c) Collin D. Brooks <collin.brooks@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection;

use Cob\Bundle\ApiServicesBundle\Models\Config\ResponseModelCollectionConfig;
use Symfony\Component\EventDispatcher\Event;

/**
 * Base Event for all BaseResponseModelCollection dispatched events.
 */
abstract class ResponseModelCollectionEvent extends Event
{
    /**
     * The collection associated with this event.
     *
     * @var ResponseModelCollectionConfig
     */
    private $config;

    /**
     * Event constructor.
     */
    public function __construct(
        ResponseModelCollectionConfig $config
    ) {
        $this->config = $config;
    }

    /**
     * @return ResponseModelCollectionConfig|null
     */
    public function getConfig(): ResponseModelCollectionConfig
    {
        return $this->config;
    }

    /**
     * Get the response model's FQCN.
     *
     * @return string
     */
    public function getResponseModelClass(): string
    {
        return $this->getConfig()->getResponseModelClass();
    }
}
