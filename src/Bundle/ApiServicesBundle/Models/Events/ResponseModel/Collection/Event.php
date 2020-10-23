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

use Cob\Bundle\ApiServicesBundle\Models\ResponseModelCollectionInterface;
use Symfony\Component\EventDispatcher\Event as DispatcherEvent;

/**
 * Base Event for all ResponseModelCollectionInterface dispatched events.
 */
abstract class Event extends DispatcherEvent
{
    /**
     * The collection associated with this event.
     *
     * @var ResponseModelCollectionInterface|null
     */
    private $collection;

    /**
     * Event constructor.
     */
    public function __construct(
        ResponseModelCollectionInterface $collection = null
    ) {
        $this->collection = $collection;
    }

    /**
     * Get the ResponseModelCollectionInterface associated with this event.
     */
    public function getCollection(): ResponseModelCollectionInterface
    {
        return $this->collection;
    }
}
