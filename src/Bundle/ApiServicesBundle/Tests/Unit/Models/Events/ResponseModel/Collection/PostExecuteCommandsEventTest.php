<?php

namespace Cob\Bundle\ApiServicesBundle\Tests\Unit\Models\Events\ResponseModel\Collection;

use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\CommandFulfilledEvent;
use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\PostCountEvent;
use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\PostExecuteCommandsEvent;
use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\PostLoadEvent;
use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\PreCountEvent;
use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\PreExecuteCommandsEvent;
use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\PreLoadEvent;
use Cob\Bundle\ApiServicesBundle\Tests\Unit\Mocks\PersonCollection;
use GuzzleHttp\Command\Command;
use GuzzleHttp\Promise\FulfilledPromise;
use PHPUnit\Framework\TestCase;

/**
 * @codeCoverageIgnore
 * @coversDefaultClass \Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\PostExecuteCommandsEvent
 * @uses \Cob\Bundle\ApiServicesBundle\Models\BaseResponseModelCollection
 * @covers \Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\ResponseModelCollectionEvent
 * @uses \Cob\Bundle\ApiServicesBundle\Models\ResponseModelCollectionConfig
 * @uses \CoB\Bundle\ApiServicesBundle\Models\ResponseModelConfigSharedTrait
 */
class PostExecuteCommandsEventTest extends TestCase
{
    /**
     * @covers ::__construct
     * @covers ::getConfig
     * @covers ::getCommands
     * @covers ::getResponse
     * @covers ::setResponse
     */
    public function testGettersAndSetters()
    {
        $config = PersonCollection::getConfig();
        $commands = [new Command($config->getCommand(), $config->getDefaultArgs())];
        $combinedResponse = ['foo' => 'bar'];
        $combinedResponseNew = ['boo' => 'baz'];

        $event = new PostExecuteCommandsEvent($config, $commands, $combinedResponse);

        $this->assertEquals($config, $event->getConfig());
        $this->assertEquals($commands, $event->getCommands());
        $this->assertEquals($combinedResponse, $event->getResponse());

        $event->setResponse($combinedResponseNew);
        $this->assertEquals($combinedResponseNew, $event->getResponse());
    }
}