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
use Cob\Bundle\ApiServicesBundle\Models\ServiceClientInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Command\Guzzle\Description;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;

/**
 * @codeCoverageIgnore
 */
trait ServiceClientMockTrait
{
    public function getServiceClientMock(array $responses = null): ServiceClient
    {
        // Create a mock and queue responses.
        $mockHandler = new MockHandler($responses);

        $handlerStack = HandlerStack::create($mockHandler);
        $client = new Client(['handler' => $handlerStack]);

        $serviceConfig = $this->getTestServiceConfigInstance();
        $description = $this->getTestDescriptionInstance();

        return new ServiceClient(
            $client,
            $description,
            new Serializer($description, []),
            new Deserializer($description, true, [], false),
            null,
            $serviceConfig
        );
    }

    public function getTestServiceConfigInstance()
    {
        static $serviceConfig;

        if (is_null($serviceConfig)) {
            $serviceConfig = ServiceClient::getServiceConfig(__DIR__ . '/Resources/test.description.yml');
        }

        return $serviceConfig;
    }

    public function getTestDescriptionInstance(): Description
    {
        static $description;

        if(is_null($description)) {
            $description =  new Description($this->getTestServiceConfigInstance());
        }

        return $description;
    }

    public function getServiceClientMockWithJsonData(array $mockResponseDataFiles): ServiceClientInterface {

        return $this->getServiceClientMock(
            array_map(
                function (string $fileName) {
                    return new Response(200, [], $this->getMockResponseDataFromFile($fileName));
                },
                $mockResponseDataFiles
            )
        );
    }

    public function getMockResponseDataFromFile(string $mockResponseDataFile): string
    {
        return file_get_contents($mockResponseDataFile);
    }

    public function getServiceClientMockWithResponseData(array $data): ServiceClientInterface
    {
        return $this->getServiceClientMock([
            new Response(200, [], json_encode($data))
        ]);
    }
}
