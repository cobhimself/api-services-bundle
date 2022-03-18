<?php

namespace Cob\Bundle\ApiServicesBundle\Tests\Unit\Models\Events\ResponseModel\Collection;

use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\PreGetLoadCommandEvent;
use Cob\Bundle\ApiServicesBundle\Models\Loader\Config\CollectionLoadConfig;
use Cob\Bundle\ApiServicesBundle\Tests\ServiceClientMockTrait;
use Cob\Bundle\ApiServicesBundle\Tests\Unit\Mocks\PersonCollection;
use PHPUnit\Framework\TestCase;

/**
 * @codeCoverageIgnore
 * @coversDefaultClass \Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\PreGetLoadCommandEvent
 * @uses \Cob\Bundle\ApiServicesBundle\Models\BaseResponseModelCollection
 * @covers \Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\ResponseModelCollectionEvent
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Config\ResponseModelCollectionConfig
 * @uses \CoB\Bundle\ApiServicesBundle\Models\Config\ResponseModelConfigSharedTrait
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Deserializer
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Loader\Config\CollectionLoadConfig
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Loader\Config\LoadConfigBuilderSharedTrait
 * @uses \Cob\Bundle\ApiServicesBundle\Models\ServiceClient
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Loader\Config\LoadConfigSharedTrait
 */
class PreGetLoadCommandEventTest extends TestCase
{
    use ServiceClientMockTrait;

    /**
     * @covers ::__construct
     * @covers ::getConfig
     * @covers ::getLoadConfig
     * @covers ::setCollectionLoadConfig
     */
    public function testGettersAndSetters()
    {
        $config = PersonCollection::getConfig();
        $commandArgs = ['foo' => 'bar'];
        $commandArgsNew = ['baz' => 'blah'];

        $loadConfig = new CollectionLoadConfig(
            $this->getServiceClientMock([]),
            $commandArgs
        );

        $loadConfigNew = new CollectionLoadConfig(
            $this->getServiceClientMock([]),
            $commandArgsNew
        );

        $event = new PreGetLoadCommandEvent($config, $loadConfig);

        $this->assertEquals($config, $event->getConfig());
        $this->assertEquals($loadConfig, $event->getLoadConfig());

        //Simulate setting a new load config:
        $event->setCollectionLoadConfig($loadConfigNew);

        $this->assertEquals($loadConfigNew, $event->getLoadConfig());
        $this->assertEquals(
            $commandArgsNew,
            $event->getLoadConfig()->getCommandArgs()
        );
    }
}
