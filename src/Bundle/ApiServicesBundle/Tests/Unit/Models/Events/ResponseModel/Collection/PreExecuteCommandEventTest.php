<?php

namespace Cob\Bundle\ApiServicesBundle\Tests\Unit\Models\Events\ResponseModel\Collection;

use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\PreExecuteCommandEvent;
use Cob\Bundle\ApiServicesBundle\Tests\Unit\Mocks\PersonCollection;
use GuzzleHttp\Command\Command;
use PHPUnit\Framework\TestCase;

/**
 * @codeCoverageIgnore
 * @coversDefaultClass \Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\PreExecuteCommandEvent
 * @uses \Cob\Bundle\ApiServicesBundle\Models\BaseResponseModelCollection
 * @covers \Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\ResponseModelCollectionEvent
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Config\ResponseModelCollectionConfig
 * @uses \CoB\Bundle\ApiServicesBundle\Models\Config\ResponseModelConfigSharedTrait
 */
class PreExecuteCommandEventTest extends TestCase
{
    /**
     * @covers ::__construct
     * @covers ::getConfig
     * @covers ::getCommand
     * @covers ::setCommand
     */
    public function testGettersAndSetters()
    {
        $config = PersonCollection::getConfig();
        $command = new Command($config->getCommand(), $config->getDefaultArgs());
        //Same command but different instance; good enough for testing
        $commandNew = new Command($config->getCommand(), $config->getDefaultArgs());

        $event = new PreExecuteCommandEvent($config, $command);

        $this->assertEquals($config, $event->getConfig());
        $this->assertEquals($command, $event->getCommand());

        $event->setCommand($commandNew);
        $this->assertEquals($commandNew, $event->getCommand());
    }
}
