<?php

namespace Cob\Bundle\ApiServicesBundle\Tests\Unit\Models\Events\ResponseModel\Collection;

use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\PreLoadEvent;
use Cob\Bundle\ApiServicesBundle\Models\Loader\Config\CollectionLoadConfig;
use Cob\Bundle\ApiServicesBundle\Tests\ServiceClientMockTrait;
use Cob\Bundle\ApiServicesBundle\Tests\Unit\Mocks\PersonCollection;
use PHPUnit\Framework\TestCase;

/**
 * @codeCoverageIgnore
 * @coversDefaultClass \Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\PreLoadEvent
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Response\Collection\BaseResponseModelCollection
 * @covers \Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\ResponseModelCollectionEvent
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Config\ResponseModelCollectionConfig
 * @uses \CoB\Bundle\ApiServicesBundle\Models\Config\ResponseModelConfigSharedTrait
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Deserializer
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Loader\Config\CollectionLoadConfig
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Loader\Config\LoadConfigSharedTrait
 * @uses \Cob\Bundle\ApiServicesBundle\Models\ServiceClient
 */
class PreLoadEventTest extends TestCase
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
        $commandArgs = ['foo' => 'bar'];
        $config = PersonCollection::getConfig();
        $loadConfig = new CollectionLoadConfig($this->getServiceClientMock([]));
        $loadConfigNew = new CollectionLoadConfig($this->getServiceClientMock([]), $commandArgs);

        $event = new PreLoadEvent($config, $loadConfig);

        $this->assertEquals($config, $event->getConfig());
        $this->assertEquals($loadConfig, $event->getLoadConfig());

        $event->setCollectionLoadConfig($loadConfigNew);
        $this->assertEquals($loadConfigNew, $event->getLoadConfig());
        $this->assertEquals($commandArgs, $event->getLoadConfig()->getCommandArgs());
    }
}
