<?php

namespace Cob\Bundle\ApiServicesBundle\Exceptions;

use RuntimeException;
use Throwable;

class LoadConfigRequiredPropertyException extends RuntimeException
{
    public function __construct($property = "", $code = 0, Throwable $previous = null)
    {
        $message = "Could not obtain the required load configuration property '$property'.";

        parent::__construct($message, $code, $previous);
    }

}