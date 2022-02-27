<?php

namespace Cob\Bundle\ApiServicesBundle\Models\Loader;

use Cob\Bundle\ApiServicesBundle\Models\ResponseModelCollection;
use Cob\Bundle\ApiServicesBundle\Models\ResponseModelCollectionConfig;
use Cob\Bundle\ApiServicesBundle\Models\ServiceClientInterface;

interface CollectionLoaderInterface
{
    public static function load(
        ResponseModelCollectionConfig $config,
        ServiceClientInterface $client,
        array $commandArgs = [],
        array $countCommandArgs = [],
        array $data = []
    ): ResponseModelCollection;
}