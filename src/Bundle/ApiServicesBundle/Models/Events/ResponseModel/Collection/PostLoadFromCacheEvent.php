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

/**
 * Run when a ResponseModelCollectionInterface instance is loaded from cache.
 */
class PostLoadFromCacheEvent extends Event
{
    const NAME = 'api_services.response_model.collection.post_load_from_cache';

    /**
     * @var array the responses loaded from cache
     */
    private $responses;

    /**
     * Run after our responses have been retrieved from cache.
     *
     * @param ResponseModelCollectionInterface $collection
     * @param array                            $responses
     */
    public function __construct(
        ResponseModelCollectionInterface $collection,
        array $responses
    ) {
        $this->responses = $responses;
        parent::__construct($collection);
    }

    /**
     * @return array the response data loaded from cache
     */
    public function getResponseData(): array
    {
        return $this->responses;
    }
}
