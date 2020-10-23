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

use GuzzleHttp\Exception\BadResponseException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Handles response data without performing any deserialization.
 */
class RawResult implements ClassResultInterface
{
    /** @var string */
    private $source;

    /**
     * @inheritDoc
     */
    public static function fromResponse(
        ResponseInterface $response,
        RequestInterface $request
    ): ClassResultInterface {
        if (204 === $response->getStatusCode()) {
            throw new BadResponseException(
                'Response contains no data.',
                $request,
                $response
            );
        }

        $result = new self();

        return $result->init($response);
    }

    /**
     * Initialize our raw data
     */
    protected function init(ResponseInterface $response): RawResult
    {
        $this->source = (string) $response->getBody();

        return $this;
    }

    /**
     * Get the raw data.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->source;
    }
}
