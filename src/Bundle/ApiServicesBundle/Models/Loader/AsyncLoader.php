<?php

namespace Cob\Bundle\ApiServicesBundle\Models\Loader;

use Cob\Bundle\ApiServicesBundle\Models\Loader\State\LoadState;
use Cob\Bundle\ApiServicesBundle\Models\ResponseModel;
use Cob\Bundle\ApiServicesBundle\Models\ResponseModelConfig;
use Cob\Bundle\ApiServicesBundle\Models\ServiceClientInterface;

class AsyncLoader extends AbstractLoader
{
    /**
     * Obtain a response model whose data will be loaded asynchronously the first time data is attempted to be retrieved
     * from the model.
     *
     * @param ResponseModelConfig    $config      the response model config used to load this model
     * @param ServiceClientInterface $client      the service client used to load data
     * @param array                  $commandArgs the command arguments to use when loading
     * @param array $data
     * @return ResponseModel
     */
    public static function load(
        ResponseModelConfig $config,
        ServiceClientInterface $client,
        array $commandArgs = [],
        array $data = [],
        $parent = null
    ): ResponseModel {
        $promise = static::getLoadPromise(
            $config,
            $client,
            $commandArgs
        );

        return self::getNewResponseClass(
            $config,
            $client,
            LoadState::waiting(),
            $promise,
            $parent
        );
    }
}