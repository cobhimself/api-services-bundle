<?php

namespace Cob\Bundle\ApiServicesBundle\Tests\Unit\Models\Events\ResponseModel\Collection;

use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\PostAddModelToCollectionEvent;
use Cob\Bundle\ApiServicesBundle\Tests\ServiceClientMockTrait;
use Cob\Bundle\ApiServicesBundle\Tests\Unit\Mocks\Person;
use Cob\Bundle\ApiServicesBundle\Tests\Unit\Mocks\PersonCollection;
use PHPUnit\Framework\TestCase;

/**
 * @codeCoverageIgnore
 * @coversDefaultClass \Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\PostAddModelToCollectionEvent
 * @uses \Cob\Bundle\ApiServicesBundle\Models\BaseResponseModelCollection
 * @covers \Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\ResponseModelCollectionEvent
 * @uses \Cob\Bundle\ApiServicesBundle\Models\ResponseModelCollectionConfig
 * @uses \CoB\Bundle\ApiServicesBundle\Models\ResponseModelConfigSharedTrait
 * @uses \Cob\Bundle\ApiServicesBundle\Models\BaseResponseModel
 * @uses \Cob\Bundle\ApiServicesBundle\Models\DotData
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Deserializer
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Loader\AbstractLoader
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Loader\LoadState
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Loader\WithDataLoader
 * @uses \Cob\Bundle\ApiServicesBundle\Models\ResponseModelConfig
 * @uses \Cob\Bundle\ApiServicesBundle\Models\ServiceClient
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Util\ClassUtil
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Loader\Config\LoadConfig
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Loader\Config\LoadConfigBuilder
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Loader\Config\LoadConfigSharedTrait
 */
class PostAddModelToCollectionEventTest extends TestCase
{
    use ServiceClientMockTrait;

    /**
     * @covers ::__construct
     * @covers ::getConfig
     * @covers ::getModel
     */
    public function testGettersAndSetters()
    {
        $config = PersonCollection::getConfig();
        $model = Person::using($this->getServiceClientMock())->withData([]);

        $event = new PostAddModelToCollectionEvent($config, $model);

        $this->assertEquals($config, $event->getConfig());
        $this->assertEquals($model, $event->getModel());
    }
}