<?php

namespace Cob\Bundle\ApiServicesBundle\Tests\Unit\Models\Events\ResponseModel;

use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\ResponseModelPostLoadEvent;
use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\ResponseModelPreGetLoadCommandEvent;
use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\ResponseModelPreLoadEvent;
use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\ResponseModelPreLoadFromCacheEvent;
use Cob\Bundle\ApiServicesBundle\Tests\ServiceClientMockTrait;
use Cob\Bundle\ApiServicesBundle\Tests\Unit\BaseResponseModelTestCase;
use Cob\Bundle\ApiServicesBundle\Tests\Unit\Mocks\MockBaseResponseModel;

/**
 * @codeCoverageIgnore
 * @coversDefaultClass \Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\ResponseModelPreLoadFromCacheEvent
 * @uses \Cob\Bundle\ApiServicesBundle\Models\ServiceClient
 * @uses \Cob\Bundle\ApiServicesBundle\Models\ResponseModelConfig
 * @uses \Cob\Bundle\ApiServicesBundle\Models\BaseResponseModel
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Deserializer
 */
class ResponseModelPreLoadFromCacheEventTest extends BaseResponseModelTestCase
{
    use ServiceClientMockTrait;

    /**
     * @covers ::__construct
     * @covers \Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\ResponseModelEvent
     * @covers ::getConfig
     * @covers ::getHash
     * @covers ::setHash
     */
    public function testGettersAndSetters()
    {
        $hash = 'hashOne';
        $otherHash = 'hashTwo';

        $config = MockBaseResponseModel::getConfig();

        $event = new ResponseModelPreLoadFromCacheEvent($config, $hash);

        $this->assertEquals($config, $event->getConfig());
        $this->assertEquals($hash, $event->getHash());

        $event->setHash($otherHash);
        $this->assertEquals($otherHash, $event->getHash());
    }
}
