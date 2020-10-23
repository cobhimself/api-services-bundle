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

use GuzzleHttp\Command\Exception\CommandException;

/**
 * Thrown when a response model's data does not pass validation.
 */
class ValidationException extends CommandException
{
}
