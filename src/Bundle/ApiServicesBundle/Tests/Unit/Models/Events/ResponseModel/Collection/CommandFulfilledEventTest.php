<?php

namespace Cob\Bundle\ApiServicesBundle\Tests\Unit\Models\Events\ResponseModel\Collection;

use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\CommandFulfilledEvent;
use Cob\Bundle\ApiServicesBundle\Tests\Unit\Mocks\PersonCollection;
use GuzzleHttp\Command\Command;
use GuzzleHttp\Promise\FulfilledPromise;
use PHPUnit\Framework\TestCase;

/**
 * @codeCoverageIgnore
 * @coversDefaultClass \Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\CommandFulfilledEvent
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Response\Collection\BaseResponseModelCollection
 * @covers \Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\ResponseModelCollectionEvent
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Config\ResponseModelCollectionConfig
 * @uses \CoB\Bundle\ApiServicesBundle\Models\Config\ResponseModelConfigSharedTrait
 */
class CommandFulfilledEventTest extends TestCase
{
    /**
     * @covers ::__construct
     * @covers ::getConfig
     * @covers ::getCommands
     * @covers ::getIndex
     * @covers ::getValue
     * @covers ::getAggregate
     */
    public function testGettersAndSetters()
    {
        $config = PersonCollection::getConfig();
        $commands = [new Command($config->getCommand(), $config->getDefaultArgs())];
        $index = 0;
        $value = ['test'];

        //Normally, the aggregate promise contains a group of command promises but, for testing
        //purposes, we can simply use a fulfilled promise
        $aggregate = new FulfilledPromise('whatever');

        $event = new CommandFulfilledEvent($config, $commands, 0, $value, $aggregate);

        $this->assertEquals($config, $event->getConfig());
        $this->assertEquals($commands, $event->getCommands());
        $this->assertEquals($index, $event->getIndex());
        $this->assertEquals($value, $event->getValue());
        $this->assertEquals($aggregate, $event->getAggregate());
    }
}
