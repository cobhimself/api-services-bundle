<?php

namespace Cob\Bundle\ApiServicesBundle\Models\Loader;

use Cob\Bundle\ApiServicesBundle\Models\Loader\Config\CollectionLoadConfig;
use Cob\Bundle\ApiServicesBundle\Models\ResponseModelCollection;
use Cob\Bundle\ApiServicesBundle\Models\ResponseModelCollectionConfig;

class AsyncCollectionLoader extends AbstractCollectionLoader
{
    /**
     * Obtain a response model whose data will be loaded asynchronously the first time data is attempted to be retrieved
     * from the model.
     *
     * @param ResponseModelCollectionConfig $config the response model config used to load this model
     * @param CollectionLoadConfig $loadConfig
     * @return ResponseModelCollection
     */
    public static function load(
        ResponseModelCollectionConfig $config,
        CollectionLoadConfig $loadConfig
    ): ResponseModelCollection {
        $promise = static::getLoadPromise(
            $config,
            $loadConfig
        );

        return self::getNewResponseCollectionClass(
            $config,
            $loadConfig,
            LoadState::waiting(),
            $promise
        );
    }
}