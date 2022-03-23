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

use Cob\Bundle\ApiServicesBundle\Exceptions\InvalidResponseModel;
use Cob\Bundle\ApiServicesBundle\Models\Util\Promise;

/**
 * Run after a single ResponseModel is loaded as a part of an aggregate set
 * of promises.
 *
 * @see Promise::all()
 */
class PostRunPromiseInAllEvent extends PromiseEvent
{
    const NAME = 'api_services.promise.collection.post_run_promise_in_all';

    /**
     * The index of the Promise which was just resolved.
     *
     * @var int|null
     */
    private $index;

    /**
     * @var int|null the size of the collection of promises being loaded
     */
    private $collectionSize;

    /**
     * The value returned from the promise associated with this event.
     *
     * @var mixed|null
     */
    private $value;

    /**
     * Run after a promise within a collection of promises has resolved.
     *
     * @param mixed|null $value           the value the promise was resolved with
     * @param int|null   $index           the current index of this promise within the collection
     * @param int|null   $collectionSize  the size of the collection of promises
     * @param mixed|null $context         the context for this operation; can be an object but must be a
     *                                    valid response model
     *
     * @throws InvalidResponseModel if $context is an object but not a valid response model
     */
    public function __construct(
        $value = null,
        int $index = null,
        int $collectionSize = null,
        $context = null
    ) {
        $this->index = $index;
        $this->collectionSize = $collectionSize;
        $this->value = $value;

        parent::__construct($context);
    }

    /**
     * Get the index of this promise from within the total list of promises.
     *
     * @return int|null
     */
    public function getIndex()
    {
        return $this->index;
    }

    /**
     * Get the size of the list of promises being loaded.
     *
     * @return int|null
     */
    public function getCollectionSize()
    {
        return $this->collectionSize;
    }

    /**
     * Get the value returned from the promise associated with this event.
     *
     * @return mixed|null
     */
    public function getValue()
    {
        return $this->value;
    }
}
