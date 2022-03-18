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

use Cob\Bundle\ApiServicesBundle\Models\Events\CanGetHashTrait;
use Cob\Bundle\ApiServicesBundle\Models\Config\ResponseModelCollectionConfig;

/**
 * Run when a ResponseModelCollectionInterface instance is loaded from cache.
 */
class PostLoadFromCacheEvent extends ResponseModelCollectionEvent
{
    use CanGetHashTrait;

    const NAME = 'api_services.response_model.collection.post_load_from_cache';

    /**
     * @var array the response data loaded from cache
     */
    private $response;

    /**
     * Run after our responses have been retrieved from cache.
     *
     * @param ResponseModelCollectionConfig $config
     * @param string                        $hash
     * @param array                         $response
     */
    public function __construct(
        ResponseModelCollectionConfig $config,
        string $hash,
        array $response
    ) {
        parent::__construct($config);

        $this->response = $response;
        $this->hash = $hash;
    }

    /**
     * @return array the response data loaded from cache
     */
    public function getResponseData(): array
    {
        return $this->response;
    }
}
