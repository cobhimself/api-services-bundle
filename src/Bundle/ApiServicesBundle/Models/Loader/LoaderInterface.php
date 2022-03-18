<?php

namespace Cob\Bundle\ApiServicesBundle\Models\Loader;

use Cob\Bundle\ApiServicesBundle\Models\Loader\Config\LoadConfig;
use Cob\Bundle\ApiServicesBundle\Models\ResponseModel;
use Cob\Bundle\ApiServicesBundle\Models\Config\ResponseModelConfig;

interface LoaderInterface
{
    public static function load(
        ResponseModelConfig $config,
        LoadConfig $loadConfig
    ): ResponseModel;
}
