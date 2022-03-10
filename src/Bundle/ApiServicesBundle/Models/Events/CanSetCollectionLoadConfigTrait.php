<?php

namespace Cob\Bundle\ApiServicesBundle\Models\Events;

use Cob\Bundle\ApiServicesBundle\Models\Loader\Config\CollectionLoadConfig;

trait CanSetCollectionLoadConfigTrait
{
    use HoldsCollectionLoadConfigTrait;

    public function setCollectionLoadConfig(CollectionLoadConfig $loadConfig)
    {
        $this->loadConfig = $loadConfig;
    }
}