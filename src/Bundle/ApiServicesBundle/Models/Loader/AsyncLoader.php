<?php

namespace Cob\Bundle\ApiServicesBundle\Models\Loader;

use Cob\Bundle\ApiServicesBundle\Models\Config\ResponseModelConfig;
use Cob\Bundle\ApiServicesBundle\Models\Loader\Config\LoadConfig;
use Cob\Bundle\ApiServicesBundle\Models\Response\ResponseModel;

class AsyncLoader extends AbstractLoader
{
    /**
     * Obtain a response model whose data will be loaded asynchronously the first time data is attempted to be retrieved
     * from the model.
     *
     * @param ResponseModelConfig $config     the response model config used to load this model
     * @param LoadConfig          $loadConfig the load-time configuration to use
     *
     * @return ResponseModel
     */
    public static function load(
        ResponseModelConfig $config,
        LoadConfig $loadConfig
    ): ResponseModel {
        $promise = static::getLoadPromise(
            $config,
            $loadConfig
        );

        return self::getNewResponseClass(
            $config,
            $loadConfig,
            LoadState::waiting(),
            $promise
        );
    }
}
