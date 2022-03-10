<?php

namespace Cob\Bundle\ApiServicesBundle\Models\Events;

use Cob\Bundle\ApiServicesBundle\Models\Loader\Config\CollectionLoadConfig;

trait HoldsCollectionLoadConfigTrait
{
    /**
     * @var CollectionLoadConfig
     */
    private $loadConfig;
}