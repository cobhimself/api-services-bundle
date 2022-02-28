<?php

namespace Cob\Bundle\ApiServicesBundle\Tests\Unit\Models\Events\ResponseModel\Collection;

use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\CommandFulfilledEvent;
use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\PreLoadEvent;
use Cob\Bundle\ApiServicesBundle\Tests\Unit\Mocks\PersonCollection;
use GuzzleHttp\Command\Command;
use GuzzleHttp\Promise\FulfilledPromise;
use PHPUnit\Framework\TestCase;

/**
 * @codeCoverageIgnore
 * @coversDefaultClass \Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\PreLoadEvent
 * @uses \Cob\Bundle\ApiServicesBundle\Models\BaseResponseModelCollection
 * @covers \Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\ResponseModelCollectionEvent
 * @uses \Cob\Bundle\ApiServicesBundle\Models\ResponseModelCollectionConfig
 * @uses \CoB\Bundle\ApiServicesBundle\Models\ResponseModelConfigSharedTrait
 */
class PreLoadEventTest extends TestCase
{
    /**
     * @covers ::__construct
     * @covers ::getConfig
     * @covers ::getCommandArgs
     * @covers ::setCommandArgs
     */
    public function testGettersAndSetters()
    {
        $config = PersonCollection::getConfig();
        $commandArgs = $config->getDefaultArgs();
        $commandArgsNew = ['foo' => 'bar'];

        $event = new PreLoadEvent($config, $commandArgs);

        $this->assertEquals($config, $event->getConfig());
        $this->assertEquals($commandArgs, $event->getCommandArgs());

        $event->setCommandArgs($commandArgsNew);
        $this->assertEquals($commandArgsNew, $event->getCommandArgs());
    }
}