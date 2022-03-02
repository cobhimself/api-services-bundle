<?php

namespace Cob\Bundle\ApiServicesBundle\Models\Loader;

use Cob\Bundle\ApiServicesBundle\Models\ResponseModel;
use Cob\Bundle\ApiServicesBundle\Models\ResponseModelConfig;
use Cob\Bundle\ApiServicesBundle\Models\ServiceClientInterface;

interface LoaderInterface
{
    public static function load(
        ResponseModelConfig $config,
        ServiceClientInterface $client,
        array $commandArgs = [],
        array $data = [],
        $parent = null
    ): ResponseModel;
}