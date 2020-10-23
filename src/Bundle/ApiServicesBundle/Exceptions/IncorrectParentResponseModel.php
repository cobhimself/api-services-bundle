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
 * Exception called when a parent model is expected to be an instance of
 * a given model but is not.
 */
class IncorrectParentResponseModel extends IncorrectResponseModel
{
    /**
     * @param string $expected the FQCN of the expected class instance
     * @param string $actual   the FQCN of the actual class instance
     */
    public function __construct(string $childModel, string $expected, string $actual)
    {
        $message = sprintf('Invalid parent model for %s', $childModel);
        parent::__construct($expected, $actual, $message);
    }
}
