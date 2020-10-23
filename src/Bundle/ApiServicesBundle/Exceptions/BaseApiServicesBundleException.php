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

use Exception;
use Throwable;

/**
 * Main exception all ApiServiceBundle exceptions stem from.
 */
class BaseApiServicesBundleException extends Exception
{
    /**
     * @param string         $message  message for the exception
     * @param Throwable|null $previous if provided, the message is prefixed to
     *                                 the message of this exception
     */
    public function __construct($message = "", Throwable $previous = null)
    {
        $code = null;

        if (null !== $previous) {
            $message = sprintf(
                '%s%s%s',
                $message,
                (($message) ? PHP_EOL : ''),
                $previous->getMessage()
            );
            $code    = $previous->getCode();
        }

        parent::__construct($message, $code, $previous);
    }
}
