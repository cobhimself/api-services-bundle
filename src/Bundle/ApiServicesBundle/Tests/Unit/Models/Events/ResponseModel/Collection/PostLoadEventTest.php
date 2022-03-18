<?php

namespace Cob\Bundle\ApiServicesBundle\Tests\Unit\Models\Events\ResponseModel\Collection;

use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\PostLoadEvent;
use Cob\Bundle\ApiServicesBundle\Models\Loader\Config\CollectionLoadConfig;
use Cob\Bundle\ApiServicesBundle\Tests\ServiceClientMockTrait;
use Cob\Bundle\ApiServicesBundle\Tests\Unit\Mocks\PersonCollection;
use PHPUnit\Framework\TestCase;

/**
 * @codeCoverageIgnore
 * @coversDefaultClass \Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\PostLoadEvent
 * @uses \Cob\Bundle\ApiServicesBundle\Models\BaseResponseModelCollection
 * @covers \Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\ResponseModelCollectionEvent
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Config\ResponseModelCollectionConfig
 * @uses \CoB\Bundle\ApiServicesBundle\Models\Config\ResponseModelConfigSharedTrait
 */
class PostLoadEventTest extends TestCase
{
    use ServiceClientMockTrait;

    /**
     * @covers ::__construct
     * @covers ::getConfig
     * @covers ::getLoadConfig
     * @covers ::getResponse
     * @covers ::setResponse
     * @uses \Cob\Bundle\ApiServicesBundle\Models\Loader\Config\CollectionLoadConfig
     * @uses \Cob\Bundle\ApiServicesBundle\Models\Loader\Config\CollectionLoadConfigBuilder
     * @uses \Cob\Bundle\ApiServicesBundle\Models\ServiceClient
     * @uses \Cob\Bundle\ApiServicesBundle\Models\Deserializer
     */
    public function testGettersAndSetters()
    {
        $config = PersonCollection::getConfig();
        $loadConfig = new CollectionLoadConfig($this->getServiceClientMock([]));
        $response = ['foo' => 'bar'];
        $responseNew = ['boo' => 'baz'];

        $event = new PostLoadEvent($config, $loadConfig, $response);

        $this->assertEquals($config, $event->getConfig());
        $this->assertEquals($loadConfig, $event->getLoadConfig());
        $this->assertEquals($response, $event->getResponse());

        $event->setResponse($responseNew);
        $this->assertEquals($responseNew, $event->getResponse());

    }
}
