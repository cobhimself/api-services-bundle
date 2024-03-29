<?php

namespace Cob\Bundle\ApiServicesBundle\Tests\Unit\Models\Events\ResponseModel;

use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\ResponseModelPostLoadFromCacheEvent;
use Cob\Bundle\ApiServicesBundle\Tests\ServiceClientMockTrait;
use Cob\Bundle\ApiServicesBundle\Tests\Unit\Mocks\MockBaseResponseModel;
use Cob\Bundle\ApiServicesBundle\Tests\Unit\Models\Response\BaseTestCase;

/**
 * @codeCoverageIgnore
 * @coversDefaultClass \Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\ResponseModelPostLoadFromCacheEvent
 * @uses \Cob\Bundle\ApiServicesBundle\Models\ServiceClient
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Config\ResponseModelConfig
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Response\BaseResponseModel
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Deserializer
 */
class ResponseModelPostLoadFromCacheEventTest extends BaseTestCase
{
    use ServiceClientMockTrait;

    /**
     * @covers ::__construct
     * @covers \Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\ResponseModelEvent
     * @covers ::getConfig
     * @covers ::getHash
     * @covers ::getCachedData
     * @covers ::setCachedData
     */
    public function testGettersAndSetters()
    {
        $cachedData = ['test', 'test'];
        $otherCachedData = ['blah'];
        $hash = 'test-hash';

        $config = MockBaseResponseModel::getConfig();

        $event = new ResponseModelPostLoadFromCacheEvent(
            $config, $hash, $cachedData
        );

        $this->assertEquals($config, $event->getConfig());
        $this->assertEquals($hash, $event->getHash());
        $this->assertSame($cachedData, $event->getCachedData());

        $event->setCachedData($otherCachedData);
        $this->assertSame($otherCachedData, $event->getCachedData());
    }
}
