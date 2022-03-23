<?php

namespace Cob\Bundle\ApiServicesBundle\Models\Loader;

use Cob\Bundle\ApiServicesBundle\Models\Loader\Config\LoadConfig;
use Cob\Bundle\ApiServicesBundle\Models\Response\ResponseModel;
use Cob\Bundle\ApiServicesBundle\Models\Config\ResponseModelConfig;
use GuzzleHttp\Promise\FulfilledPromise;

class WithDataLoader extends AbstractLoader
{
    /**
     * Obtain a response model with pre-existing data set on it.
     *
     * This is useful when you have data for the model you don't need to retrieve through the service client.
     *
     * @param ResponseModelConfig $config the response model config to use for the model we want to initialize with
     *                                            the given data.
     * @param LoadConfig $loadConfig
     * @return ResponseModel
     */
    public static function load(ResponseModelConfig $config, LoadConfig $loadConfig): ResponseModel {
        return self::getNewResponseClass(
            $config,
            $loadConfig,
            LoadState::loadedWithData(),
            new FulfilledPromise($loadConfig->getExistingData())
        );
    }
}
