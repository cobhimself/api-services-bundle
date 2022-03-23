<?php

namespace Cob\Bundle\ApiServicesBundle\Models\Loader;

use Cob\Bundle\ApiServicesBundle\Models\Config\ResponseModelConfig;
use Cob\Bundle\ApiServicesBundle\Models\Loader\Config\LoadConfig;
use Cob\Bundle\ApiServicesBundle\Models\Response\ResponseModel;

interface LoaderInterface
{
    public static function load(
        ResponseModelConfig $config,
        LoadConfig $loadConfig
    ): ResponseModel;
}
