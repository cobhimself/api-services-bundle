<?php

namespace Cob\Bundle\ApiServicesBundle\Tests\Unit\Models\Events\ResponseModel\Collection;

use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\CommandFulfilledEvent;
use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\PostLoadEvent;
use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\PreLoadEvent;
use Cob\Bundle\ApiServicesBundle\Tests\Unit\Mocks\PersonCollection;
use GuzzleHttp\Command\Command;
use GuzzleHttp\Promise\FulfilledPromise;
use PHPUnit\Framework\TestCase;

/**
 * @codeCoverageIgnore
 * @coversDefaultClass \Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\PostLoadEvent
 * @uses \Cob\Bundle\ApiServicesBundle\Models\BaseResponseModelCollection
 * @covers \Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\ResponseModelCollectionEvent
 * @uses \Cob\Bundle\ApiServicesBundle\Models\ResponseModelCollectionConfig
 * @uses \CoB\Bundle\ApiServicesBundle\Models\ResponseModelConfigSharedTrait
 */
class PostLoadEventTest extends TestCase
{
    /**
     * @covers ::__construct
     * @covers ::getConfig
     * @covers ::getCommandArgs
     * @covers ::getResponse
     * @covers ::setResponse
     */
    public function testGettersAndSetters()
    {
        $config = PersonCollection::getConfig();
        $commandArgs = $config->getDefaultArgs();
        $response = ['foo' => 'bar'];
        $responseNew = ['boo' => 'baz'];

        $event = new PostLoadEvent($config, $commandArgs, $response);

        $this->assertEquals($config, $event->getConfig());
        $this->assertEquals($commandArgs, $event->getCommandArgs());
        $this->assertEquals($response, $event->getResponse());

        $event->setResponse($responseNew);
        $this->assertEquals($responseNew, $event->getResponse());

    }
}