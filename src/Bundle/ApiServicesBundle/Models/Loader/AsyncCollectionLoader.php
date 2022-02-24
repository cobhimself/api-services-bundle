<?php

namespace Cob\Bundle\ApiServicesBundle\Models\Loader;

use Cob\Bundle\ApiServicesBundle\Models\Loader\State\LoadState;
use Cob\Bundle\ApiServicesBundle\Models\ResponseModel;
use Cob\Bundle\ApiServicesBundle\Models\ResponseModelCollection;
use Cob\Bundle\ApiServicesBundle\Models\ResponseModelCollectionConfig;
use Cob\Bundle\ApiServicesBundle\Models\ServiceClientInterface;

class AsyncCollectionLoader extends AbstractCollectionLoader
{
    /**
     * Obtain a response model whose data will be loaded asynchronously the first time data is attempted to be retrieved
     * from the model.
     *
     * @param ResponseModelCollectionConfig    $config      the response model config used to load this model
     * @param ServiceClientInterface $client      the service client used to load data
     * @param array                  $commandArgs the command arguments to use when loading
     * @param array $data
     * @return ResponseModelCollection
     */
    public static function load(
        ResponseModelCollectionConfig $config,
        ServiceClientInterface        $client,
        array                         $commandArgs = [],
        array                         $data = []
    ): ResponseModelCollection {
        $promise = static::getLoadPromise(
            $config,
            $client,
            $commandArgs
        );

        return self::getNewResponseCollectionClass(
            $config,
            $client,
            LoadState::waiting(),
            $promise
        );
    }
}