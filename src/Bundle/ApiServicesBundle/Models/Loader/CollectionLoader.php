<?php

namespace Cob\Bundle\ApiServicesBundle\Models\Loader;

use Cob\Bundle\ApiServicesBundle\Models\Loader\Config\CollectionLoadConfig;
use Cob\Bundle\ApiServicesBundle\Models\ResponseModelCollection;
use Cob\Bundle\ApiServicesBundle\Models\Config\ResponseModelCollectionConfig;
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
        CollectionLoadConfig $loadConfig
    ): ResponseModelCollection {
        $loaded = static::getLoadPromise(
            $config,
            $loadConfig
        )->wait();

        return static::getNewResponseCollectionClass(
            $config,
            $loadConfig,
            LoadState::loaded(),
            new FulfilledPromise($loaded)
        );
    }
}
