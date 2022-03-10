<?php

namespace Cob\Bundle\ApiServicesBundle\Models\Loader;

use Cob\Bundle\ApiServicesBundle\Models\Loader\Config\CollectionLoadConfig;
use Cob\Bundle\ApiServicesBundle\Models\ResponseModelCollection;
use Cob\Bundle\ApiServicesBundle\Models\ResponseModelCollectionConfig;

interface CollectionLoaderInterface
{
    public static function load(
        ResponseModelCollectionConfig $config,
        CollectionLoadConfig $loadConfig
    ): ResponseModelCollection;
}