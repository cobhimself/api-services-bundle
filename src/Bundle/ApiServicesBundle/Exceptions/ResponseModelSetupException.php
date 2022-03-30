<?php
/*
 * This file is part of the cobhimself/api-services-bundle package.
 *
 * (c) Collin D. Brooks <collin.brooks@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cob\Bundle\ApiServicesBundle\Exceptions;

/**
 * Exception thrown when a response model class has not been setup correctly.
 */
class ResponseModelSetupException extends BaseApiServicesBundleException
{
    static function confirmNotNull($modelClass, $property, $value)
    {
        if (is_null($value)) {
            throw new ResponseModelSetupException(sprintf(
                "%s expects the '%s' property to be set!",
                $modelClass ?? 'The model',
                $property
            ));
        }
    }

    static function confirmResponseModelClassSet($fqcn)
    {
        if (is_null($fqcn)) {
            throw new ResponseModelSetupException('A response model class must be provided!');
        }
    }
}
