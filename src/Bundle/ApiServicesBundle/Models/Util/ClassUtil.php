<?php

namespace Cob\Bundle\ApiServicesBundle\Models\Util;

use Cob\Bundle\ApiServicesBundle\Exceptions\IncorrectParentResponseModel;
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
        if(!static::isValidResponseModel($model)) {
            throw new InvalidResponseModel(
                static::className($model),
                [ResponseModel::class]
            );
        }
    }

    public static function confirmValidResponseModelCollection($model) {
        if(!static::isValidResponseModelCollection($model)) {
            throw new InvalidResponseModel(
                static::className($model),
                [ResponseModelCollection::class]
            );
        }
    }

    public static function confirmValidResponseModelOrCollection($model) {
        if(
            !static::isValidResponseModel($model)
            && !static::isValidResponseModelCollection($model)
        ) {
            throw new InvalidResponseModel(
                static::className($model),
                [ResponseModel::class, ResponseModelCollection::class]
            );
        }
    }

    /**
     * Confirm the model we are attempting to set as the parent model is of the
     * correct instance.
     *
     * @param string|object $parent the parent class which the model MUST be an
     *                              instance of
     * @param string|object $actual the instance to confirm
     * @param string|object $child  the child class of the parent; used during exception output
     *
     *
     * @throws IncorrectParentResponseModel if the parent class is not the same as the given actual class.
     */
    public static function confirmCorrectParentModel(
        $parent,
        $actual,
        $child
    ) {
        if (!static::isInstanceOf($actual, $parent, $child)) {
            throw new IncorrectParentResponseModel(
                $child,
                self::className($parent),
                self::className($actual)
            );
        }
    }

    public static function confirmValidParentModel($parent, $child)
    {
        if (
            !ClassUtil::isValidResponseModel($parent)
            && !ClassUtil::isValidResponseModelCollection($parent)
        ) {
            throw new IncorrectParentResponseModel(
                $child,
                ResponseModel::class . ' OR ' . ResponseModelCollection::class,
                get_class($parent)
            );
        }
    }
}
