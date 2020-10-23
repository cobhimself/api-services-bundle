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

use Cob\Bundle\ApiServicesBundle\Models\ResponseModelInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Base event for all ResponseModelInterface instances.
 */
abstract class ResponseModelEvent extends Event
{
    private $model;

    public function __construct(ResponseModelInterface $model)
    {
        $this->model = $model;
    }

    /**
     * Get the ResponseModelInterface associated with this event.
     */
    public function getModel(): ResponseModelInterface
    {
        return $this->model;
    }
}
