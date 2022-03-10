<?php

namespace Cob\Bundle\ApiServicesBundle\Models\Events;

use Cob\Bundle\ApiServicesBundle\Models\Loader\Config\CollectionLoadConfig;

trait CanGetCollectionLoadConfigTrait
{
    use HoldsCollectionLoadConfigTrait;

    public function getLoadConfig(): CollectionLoadConfig
    {
        return $this->loadConfig;
    }
}