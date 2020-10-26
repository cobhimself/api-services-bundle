<?php
/*
 * This file is part of the cobhimself/api-services-bundle package.
 *
 * (c) Collin D. Brooks <collin.brooks@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cob\Bundle\ApiServicesBundle\Tests;

use Cob\Bundle\ApiServicesBundle\Models\ServiceClient;
use GuzzleHttp\Client;
use GuzzleHttp\Command\Guzzle\Description;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;

trait ServiceClientMockTrait
{
    public function getServiceClientMock(array $responses = null)
    {
        // Create a mock and queue two responses.
        $mockHandler = new MockHandler($responses);

        $handlerStack = HandlerStack::create($mockHandler);
        $client = new Client(['handler' => $handlerStack]);

        $description = new Description([]);
        return new ServiceClient($client, $description);
    }
}