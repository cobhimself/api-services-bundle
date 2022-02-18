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

use Cob\Bundle\ApiServicesBundle\Models\ResponseModelConfig;
use GuzzleHttp\Command\CommandInterface;
use Cob\Bundle\ApiServicesBundle\Models\AbstractResponseModel;
use Cob\Bundle\ApiServicesBundle\Models\ResponseModelInterface;

/**
 * Run right before a response model is to be loaded from cache.
 *
 * @see AbstractResponseModel::load()
 */
class ResponseModelPreLoadFromCacheEvent extends ResponseModelEvent
{
    const NAME = 'api_services.response_model.pre_load_from_cache';

    /**
     * The hash being used to load data for the model from cache.
     *
     * @var string
     */
    protected $hash;

    public function __construct(
        ResponseModelConfig $config,
        string $hash
    ) {
        $this->hash = $hash;

        parent::__construct($config);
    }

    public function getHash(): string
    {
        return $this->hash;
    }

    public function setHash(string $newHash)
    {
        $this->hash = $newHash;
    }
}
