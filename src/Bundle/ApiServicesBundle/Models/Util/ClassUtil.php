<?php

namespace Cob\Bundle\ApiServicesBundle\Models\Util;

use Cob\Bundle\ApiServicesBundle\Exceptions\InvalidResponseModel;
use Cob\Bundle\ApiServicesBundle\Models\ResponseModel;
use Cob\Bundle\ApiServicesBundle\Models\ResponseModelCollection;

class ClassUtil
{
    /**
     * Determine whether or not the given model is an instance of the interface
     *
     * @param string|object $model     an object or the FQCN of a class to
     *                                 determine whether or not it is an
     *                                 instance of the given interface
     * @param string|object $interface an object or the FQCN of a class which we
     *                                 want the model to be an instance of
     *
     * @return bool
     * @noinspection PhpMissingParamTypeInspection
     */
    public static function isInstanceOf($model, $interface): bool
    {
        $interface = static::className($interface);
        $model = static::className($model);

        return $interface === $model || is_subclass_of($model, $interface);
    }

    /**
     * Return the FQCN of a model regardless of whether or not it is an object
     * or string.
     *
     * @param mixed $model the model to obtain the FQCN of
     */
    public static function className($model): string
    {
        return is_string($model) ? $model : get_class($model);
    }

    public static function isValidResponseModel($model): bool
    {
        return static::isInstanceOf($model, ResponseModel::class);
    }

    public static function isValidResponseModelCollection($model): bool
    {
        return static::isInstanceOf($model, ResponseModelCollection::class);
    }

    public static function confirmValidResponseModel($model)
    {
        if(!$fqcn = static::isValidResponseModel($model)) {
            throw new InvalidResponseModel($fqcn);
        }
    }

    public static function confirmValidResponseModelCollection($model) {
        if(!$fqcn = static::isValidResponseModelCollection($model)) {
            throw new InvalidResponseModel($fqcn);
        }
    }
}