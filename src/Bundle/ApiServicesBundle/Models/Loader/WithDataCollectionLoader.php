<?php

namespace Cob\Bundle\ApiServicesBundle\Models\Loader;

use Cob\Bundle\ApiServicesBundle\Models\Loader\Config\CollectionLoadConfig;
use Cob\Bundle\ApiServicesBundle\Models\Response\Collection\ResponseModelCollection;
use Cob\Bundle\ApiServicesBundle\Models\Config\ResponseModelCollectionConfig;
use GuzzleHttp\Promise\FulfilledPromise;

class WithDataCollectionLoader extends AbstractCollectionLoader
{
    /**
     * Obtain a response model with pre-existing data set on it.
     *
     * This is useful when you have data for the model you don't need to retrieve through the service client.
     *
     * @param ResponseModelCollectionConfig $config the response model config to use for the model we want to
     *                                                        initialize with the given data.
     * @param CollectionLoadConfig $loadConfig
     * @return ResponseModelCollection
     */
    public static function load(
        ResponseModelCollectionConfig $config,
        CollectionLoadConfig $loadConfig
    ): ResponseModelCollection {
        return self::getNewResponseCollectionClass(
            $config,
            $loadConfig,
            LoadState::loadedWithData(),
            new FulfilledPromise($loadConfig->getExistingData())
        );
    }
}
