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

use Cob\Bundle\ApiServicesBundle\Models\Config\ResponseModelCollectionConfig;
use Cob\Bundle\ApiServicesBundle\Models\Events\CanGetCollectionLoadConfigTrait;
use Cob\Bundle\ApiServicesBundle\Models\Events\CanSetCollectionLoadConfigTrait;
use Cob\Bundle\ApiServicesBundle\Models\Loader\Config\CollectionLoadConfig;

/**
 * Run before any loading is done in the collection.
 */
class PreLoadEvent extends ResponseModelCollectionEvent
{
    use CanGetCollectionLoadConfigTrait;
    use CanSetCollectionLoadConfigTrait;

    const NAME = 'api_services.response_model.collection.pre_load';

    public function __construct(ResponseModelCollectionConfig $config, CollectionLoadConfig $loadConfig)
    {
        parent::__construct($config);
        $this->loadConfig = $loadConfig;
    }
}
