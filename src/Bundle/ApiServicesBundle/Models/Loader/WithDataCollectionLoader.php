<?php

namespace Cob\Bundle\ApiServicesBundle\Models\Loader;

use Cob\Bundle\ApiServicesBundle\Models\Loader\State\LoadState;
use Cob\Bundle\ApiServicesBundle\Models\ResponseModel;
use Cob\Bundle\ApiServicesBundle\Models\ResponseModelCollection;
use Cob\Bundle\ApiServicesBundle\Models\ResponseModelCollectionConfig;
use Cob\Bundle\ApiServicesBundle\Models\ServiceClientInterface;
use GuzzleHttp\Promise\FulfilledPromise;

class WithDataCollectionLoader extends AbstractCollectionLoader
{
    /**
     * Obtain a response model with pre-existing data set on it.
     *
     * This is useful when you have data for the model you don't need to retrieve through the service client.
     *
     * @param ResponseModelCollectionConfig $config           the response model config to use for the model we want to
     *                                                        initialize with the given data.
     * @param ServiceClientInterface        $client           the service client our model should use
     * @param array                         $commandArgs      arguments to be used with thee load command (not used
     *                                                        with this loader)
     * @param array                         $countCommandArgs arguments to be used with the count command (not used
     *                                                        with this loader)
     * @param array                         $data             the data to establish in the model
     *
     * @return ResponseModelCollection
     */
    public static function load(
        ResponseModelCollectionConfig $config,
        ServiceClientInterface        $client,
        array                         $commandArgs = [],
        array                         $countCommandArgs = [],
        array                         $data = [],
                                      $parent = null
    ): ResponseModelCollection {
        return self::getNewResponseCollectionClass(
            $config,
            $client,
            LoadState::loadedWithData(),
            new FulfilledPromise($data),
            $parent
        );
    }
}