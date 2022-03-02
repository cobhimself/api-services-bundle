<?php

namespace Cob\Bundle\ApiServicesBundle\Models\Loader;

use Cob\Bundle\ApiServicesBundle\Models\Loader\State\LoadState;
use Cob\Bundle\ApiServicesBundle\Models\ResponseModel;
use Cob\Bundle\ApiServicesBundle\Models\ResponseModelConfig;
use Cob\Bundle\ApiServicesBundle\Models\ServiceClientInterface;
use GuzzleHttp\Promise\FulfilledPromise;

class WithDataLoader extends AbstractLoader
{
    /**
     * Obtain a response model with pre-existing data set on it.
     *
     * This is useful when you have data for the model you don't need to retrieve through the service client.
     *
     * @param ResponseModelConfig    $config      the response model config to use for the model we want to initialize with
     *                                            the given data.
     * @param ServiceClientInterface $client      the service client our model should use
     * @param array                  $commandArgs not used with this loader
     * @param array                  $data        the data to establish in the model
     *
     * @return ResponseModel
     */
    public static function load(
        ResponseModelConfig $config,
        ServiceClientInterface $client,
        array $commandArgs = [],
        array $data = [],
        $parent = null
    ): ResponseModel {
        return self::getNewResponseClass(
            $config,
            $client,
            LoadState::loadedWithData(),
            new FulfilledPromise($data),
            $parent
        );
    }
}