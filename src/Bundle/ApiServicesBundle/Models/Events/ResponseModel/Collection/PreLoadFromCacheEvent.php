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
use Cob\Bundle\ApiServicesBundle\Models\Events\CanSetHashTrait;
use Cob\Bundle\ApiServicesBundle\Models\Config\ResponseModelCollectionConfig;

/**
 * Run when a ResponseModelCollection instance is about to be
 * loaded from cache.
 */
class PreLoadFromCacheEvent extends ResponseModelCollectionEvent
{
    use CanGetHashTrait;
    use CanSetHashTrait;

    const NAME = 'api_services.response_model.collection.pre_load_from_cache';

    /**
     * @var string
     */
    private $hash;

    public function __construct(
        ResponseModelCollectionConfig $config,
        string $hash
    ) {
        parent::__construct($config);
        $this->hash = $hash;
    }
}
