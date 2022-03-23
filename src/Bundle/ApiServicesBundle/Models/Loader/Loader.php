<?php

namespace Cob\Bundle\ApiServicesBundle\Models\Loader;

use Cob\Bundle\ApiServicesBundle\Models\Config\ResponseModelConfig;
use Cob\Bundle\ApiServicesBundle\Models\Loader\Config\LoadConfig;
use Cob\Bundle\ApiServicesBundle\Models\Response\ResponseModel;
use GuzzleHttp\Promise\FulfilledPromise;

class Loader extends AbstractLoader
{
    /**
     * Load the data for the response model synchronously.
     *
     * @param ResponseModelConfig $config the response model configuration to use when loading the model
     * @param LoadConfig $loadConfig
     * @return ResponseModel
     */
    public static function load(
        ResponseModelConfig $config,
        LoadConfig $loadConfig
    ): ResponseModel {
        $loaded = static::getLoadPromise(
            $config,
            $loadConfig
        )->wait();

        return static::getNewResponseClass(
            $config,
            $loadConfig,
            LoadState::loaded(),
            new FulfilledPromise($loaded)
        );
    }
}
