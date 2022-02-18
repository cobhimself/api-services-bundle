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

use Cob\Bundle\ApiServicesBundle\Models\Deserializer;
use Cob\Bundle\ApiServicesBundle\Models\Serializer;
use Cob\Bundle\ApiServicesBundle\Models\ServiceClient;
use GuzzleHttp\Client;
use GuzzleHttp\Command\Guzzle\Description;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;

/**
 * @codeCoverageIgnore
 */
trait ServiceClientMockTrait
{
    public function getServiceClientMock(array $responses = null)
    {
        // Create a mock and queue responses.
        $mockHandler = new MockHandler($responses);

        $handlerStack = HandlerStack::create($mockHandler);
        $client = new Client(['handler' => $handlerStack]);

        $serviceConfig = ServiceClient::getServiceConfig(__DIR__ . '/Resources/test.description.yml');
        $description = new Description($serviceConfig);

        return new ServiceClient(
            $client,
            $description,
            new Serializer($description, []),
            new Deserializer($description, true, [], false),
            null,
            $serviceConfig
        );
    }

    public function getServiceClientMockWithResponseData(array $data)
    {
        return $this->getServiceClientMock([
            new Response(200, [], json_encode($data))
        ]);
    }
}