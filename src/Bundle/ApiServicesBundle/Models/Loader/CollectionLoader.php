<?php

namespace Cob\Bundle\ApiServicesBundle\Models\Loader;

use Cob\Bundle\ApiServicesBundle\Models\Loader\State\LoadState;
use Cob\Bundle\ApiServicesBundle\Models\ResponseModelCollection;
use Cob\Bundle\ApiServicesBundle\Models\ResponseModelCollectionConfig;
use Cob\Bundle\ApiServicesBundle\Models\ServiceClientInterface;
use GuzzleHttp\Promise\FulfilledPromise;

/**
 * Class whose responsibility is to load data into a {@link ResponseModelCollection} using a
 * {@link ServiceClientInterface} based on a {@link CollectionLoadConfiguration}.
 */
class CollectionLoader extends AbstractCollectionLoader
{
    public static function load(
        ResponseModelCollectionConfig $config,
        ServiceClientInterface $client,
        array $commandArgs = [], array $data = []
    ): ResponseModelCollection {
        $loaded = static::getLoadPromise(
            $config,
            $client,
            $commandArgs
        )->wait();

        return static::getNewResponseCollectionClass(
            $config,
            $client,
            LoadState::loaded(),
            new FulfilledPromise($loaded)
        );
    }
}