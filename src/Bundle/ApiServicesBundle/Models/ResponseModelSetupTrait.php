<?php
/*
 * This file is part of the cobhimself/api-services-bundle package.
 *
 * (c) Collin D. Brooks <collin.brooks@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cob\Bundle\ApiServicesBundle\Models;

use Cob\Bundle\ApiServicesBundle\Exceptions\IncorrectParentResponseModel;
use Cob\Bundle\ApiServicesBundle\Exceptions\InvalidResponseModel;
use Cob\Bundle\ApiServicesBundle\Exceptions\ResponseModelSetupException;

trait ResponseModelSetupTrait
{
    /**
     * Confirm the given property exists in the response model.
     *
     * This method helps us make sure our models are setup correctly and
     * fails early if they aren't.
     *
     * @param string $property the property to check
     *
     * @throws ResponseModelSetupException
     */
    protected function checkForPropertyException(string $property)
    {
        if (!property_exists($this, $property) || null === $this->$property) {
            $message = 'Could not get property \'%s\'!' . PHP_EOL . "\tIN: %s";
            throw new ResponseModelSetupException(
                sprintf(
                    $message,
                    $property,
                    get_class($this)
                )
            );
        }
    }

    /**
     * Confirm the given constant exists in this response model.
     *
     * This method allows us to confirm our model is setup correctly and fail
     * early if it is not.
     *
     * @param string $const the constant name to check for saneness
     *
     * @throws ResponseModelSetupException
     */
    protected static function checkForConstException(string $const)
    {
        $constant = 'static::' . $const;
        if (!defined($constant) || null === constant($constant)) {
            throw new ResponseModelSetupException(
                sprintf(
                    'Please set %s constant in %s',
                    $const,
                    static::class
                )
            );
        }
    }

    /**
     * Confirm the model we are attempting to set as the parent model is of the
     * correct instance.
     *
     * @param string                 $parent the FQCN of the parent class which
     *                                       the model MUST be an instance of
     * @param ResponseModelInterface $actual the instance to confirm
     *
     * @throws IncorrectParentResponseModel
     */
    protected static function confirmCorrectParentModel(
        string $parent,
        ResponseModelInterface $actual
    ) {
        if (!static::isInstanceOf($actual, $parent)) {
            throw new IncorrectParentResponseModel(
                self::class,
                $parent,
                get_class($actual)
            );
        }
    }

    /**
     * Confirm the given model implements either
     * ResponseModelCollectionInterface or ResponseModelInterface
     *
     * @param string|object $model
     *
     * @throws InvalidResponseModel
     * @noinspection PhpMissingParamTypeInspection
     */
    protected static function confirmValidResponseModel($model)
    {
        $class = static::className($model);

        if (!(
            static::isResponseCollection($model)
            || static::isResponseModel($model)
        )) {
            throw new InvalidResponseModel($class);
        }
    }

    /**
     * Return whether or not the given model is an instance of
     * ResponseModelCollectionInterface.
     *
     * @param string|object $model
     * @noinspection PhpMissingParamTypeInspection
     */
    protected static function isResponseCollection($model): bool
    {
        return static::isInstanceOf(
            $model,
            ResponseModelCollectionInterface::class
        );
    }

    /**
     * Return whether or not the given model is an instance of
     * ResponseModelInterface.
     *
     * @param string|object $model
     * @noinspection PhpMissingParamTypeInspection
     */
    protected static function isResponseModel($model): bool
    {
        return static::isInstanceOf($model, ResponseModelInterface::class);
    }

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
    protected static function isInstanceOf($model, $interface): bool
    {
        $interface = static::className($interface);
        $model = static::className($model);

        return $interface === $model || in_array(
            $interface,
            class_implements(
                $model
            ),
            true
        );
    }

    /**
     * Return the FQCN of a model regardless of whether or not it is an object
     * or string.
     *
     * @param mixed $model the model to obtain the FQCN of
     */
    protected static function className($model): string
    {
        return is_string($model) ? $model : get_class($model);
    }
}
