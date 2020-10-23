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
 * Exception called when a model is expected to be an instance of
 * a given model but is not.
 */
class IncorrectResponseModel extends BaseApiServicesBundleException
{
    /**
     * @param string $expected the FQCN of the expected class instance
     * @param string $actual   the FQCN of the actual class instance
     * @param string $message  an additional message to prepend to the exception
     */
    public function __construct(
        string $expected,
        string $actual,
        string $message = null
    ) {
        $message = (null !== $message) ? $message . ': ' : '';
        $message .= sprintf(
            '%s is not a valid %s',
            $actual,
            $expected
        );

        parent::__construct($message);
    }
}
