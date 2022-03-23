<?php

namespace Cob\Bundle\ApiServicesBundle\Models\Loader;

use Cob\Bundle\ApiServicesBundle\Models\Loader\Config\CollectionLoadConfig;
use Cob\Bundle\ApiServicesBundle\Models\Response\Collection\ResponseModelCollection;
use Cob\Bundle\ApiServicesBundle\Models\Config\ResponseModelCollectionConfig;

interface CollectionLoaderInterface
{
    public static function load(
        ResponseModelCollectionConfig $config,
        CollectionLoadConfig $loadConfig
    ): ResponseModelCollection;
}
