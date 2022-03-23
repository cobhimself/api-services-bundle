<?php
/*
 * This file is part of the cobhimself/api-services-bundle package.
 *
 * (c) Collin D. Brooks <collin.brooks@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cob\Bundle\ApiServicesBundle\Models\Http;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Interface which allows us to create deserialization classes for
 * response data.
 */
interface ClassResultInterface
{
    /**
     * @param ResponseInterface $response the response we to get the result from
     * @param RequestInterface  $request  the request which provided the response
     *
     * @return ClassResultInterface
     */
    public static function fromResponse(
        ResponseInterface $response,
        RequestInterface $request
    ): ClassResultInterface;
}
