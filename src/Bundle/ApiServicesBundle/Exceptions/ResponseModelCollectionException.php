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
 * Exception thrown when an issue occurs within a
 * ResponseModelCollection interface instance.
 *
 * All exceptions, regardless of type, are caught and rolled up into this one
 * exception within the BaseResponseModelCollection class as much
 * as possible.
 */
class ResponseModelCollectionException extends BaseApiServicesBundleException
{
}
