<?php

namespace Cob\Bundle\ApiServicesBundle\Tests\Unit\Models\Events\ResponseModel\Collection;

use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\PostExecuteCommandsEvent;
use Cob\Bundle\ApiServicesBundle\Tests\Unit\Mocks\PersonCollection;
use GuzzleHttp\Command\Command;
use PHPUnit\Framework\TestCase;

/**
 * @codeCoverageIgnore
 * @coversDefaultClass \Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\PostExecuteCommandsEvent
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Response\Collection\BaseResponseModelCollection
 * @covers \Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\ResponseModelCollectionEvent
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Config\ResponseModelCollectionConfig
 * @uses \CoB\Bundle\ApiServicesBundle\Models\Config\ResponseModelConfigSharedTrait
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
