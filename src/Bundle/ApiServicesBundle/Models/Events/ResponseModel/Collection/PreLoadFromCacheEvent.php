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
 * Run when a ResponseModelCollectionInterface instance is about to be
 * loaded from cache.
 */
class PreLoadFromCacheEvent extends Event
{
    const NAME = 'api_services.response_model.collection.pre_load_from_cache';
    /**
     * @var array
     */
    private $responseData;

    public function __construct(
        ResponseModelCollectionInterface $collection,
        array $responseData
    ) {
        parent::__construct($collection);
        $this->responseData = $responseData;
    }

    /**
     * @return array
     */
    public function getResponseData(): array
    {
        return $this->responseData;
    }

    /**
     * @param array $responseData
     *
     * @return PreLoadFromCacheEvent
     */
    public function setResponseData(
        array $responseData
    ): PreLoadFromCacheEvent {
        $this->responseData = $responseData;

        return $this;
    }
}
