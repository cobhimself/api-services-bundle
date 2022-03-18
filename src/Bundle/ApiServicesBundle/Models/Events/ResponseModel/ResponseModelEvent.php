<?php
/*
 * This file is part of the cobhimself/api-services-bundle package.
 *
 * (c) Collin D. Brooks <collin.brooks@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel;

use Cob\Bundle\ApiServicesBundle\Models\Config\ResponseModelConfig;
use Symfony\Component\EventDispatcher\Event;

/**
 * Base event for all ResponseModel instances.
 */
abstract class ResponseModelEvent extends Event
{
    /**
     * @var ResponseModelConfig
     */
    protected $responseModelConfig;

    public function __construct(ResponseModelConfig $config)
    {
        $this->responseModelConfig = $config;
    }

    /**
     * Get the ResponseModelInterface associated with this event.
     */
    public function getConfig(): ResponseModelConfig
    {
        return $this->responseModelConfig;
    }
}
