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

/**
 * Run after a ResponseModelInterface instance is loaded from cache.
 *
 * @see AbstractResponseModel::load()
 */
class ResponseModelPostLoadFromCacheEvent extends ResponseModelEvent
{
    const NAME = 'api_services.response_model.post_load_from_cache';

    /**
     * The hash string used to obtain the data from cache.
     *
     * @var string
     */
    protected $hash;

    /**
     * @var mixed
     */
    protected $cachedData;

    public function __construct(
        ResponseModelConfig $config,
        string $hash,
        $cachedData
    ) {
        $this->cachedData = $cachedData;
        $this->hash = $hash;

        parent::__construct($config);
    }

    /**
     * Return the cached data associated with this event.
     *
     * @return mixed
     */
    public function getCachedData()
    {
        return $this->cachedData;
    }

    public function setCachedData($data)
    {
        $this->cachedData = $data;
    }

    /**
     * @return string
     */
    public function getHash(): string
    {
        return $this->hash;
    }
}
