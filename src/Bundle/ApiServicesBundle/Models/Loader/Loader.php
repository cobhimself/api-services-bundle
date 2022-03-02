<?php

namespace Cob\Bundle\ApiServicesBundle\Models\Loader;

use Cob\Bundle\ApiServicesBundle\Models\Loader\State\LoadState;
use Cob\Bundle\ApiServicesBundle\Models\ResponseModel;
use Cob\Bundle\ApiServicesBundle\Models\ResponseModelConfig;
use Cob\Bundle\ApiServicesBundle\Models\ServiceClientInterface;
use GuzzleHttp\Promise\FulfilledPromise;

class Loader extends AbstractLoader
{
    /**
     * Load the data for the response model synchronously.
     *
     * @param ResponseModelConfig    $config      the response model configuration to use when loading the model
     * @param ServiceClientInterface $client      the service client to use when running commands
     * @param array                  $commandArgs arguments to provide to the command for this response model
     * @param array                  $data        ignored by this loader
     * @return ResponseModel
     */
    public static function load(
        ResponseModelConfig $config,
        ServiceClientInterface $client,
        array $commandArgs = [],
        array $data = [],
        $parent = null
    ): ResponseModel {
        $loaded = static::getLoadPromise(
            $config,
            $client,
            $commandArgs
        )->wait();

        return static::getNewResponseClass(
            $config,
            $client,
            LoadState::loaded(),
            new FulfilledPromise($loaded),
            $parent
        );
    }
}