<?php

namespace Cob\Bundle\ApiServicesBundle\Tests\Unit\Models\Events\ResponseModel;

use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\ResponseModelPostLoadEvent;
use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\ResponseModelPreGetLoadCommandEvent;
use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\ResponseModelPreLoadEvent;
use Cob\Bundle\ApiServicesBundle\Tests\ServiceClientMockTrait;
use Cob\Bundle\ApiServicesBundle\Tests\Unit\BaseResponseModelTestCase;
use Cob\Bundle\ApiServicesBundle\Tests\Unit\Mocks\MockBaseResponseModel;

/**
 * @codeCoverageIgnore
 * @coversDefaultClass \Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\ResponseModelPreLoadEvent
 * @uses \Cob\Bundle\ApiServicesBundle\Models\ServiceClient
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Config\ResponseModelConfig
 * @uses \Cob\Bundle\ApiServicesBundle\Models\BaseResponseModel
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Deserializer
 */
class ResponseModelPreLoadEventTest extends BaseResponseModelTestCase
{
    use ServiceClientMockTrait;

    /**
     * @covers ::__construct
     * @covers \Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\ResponseModelEvent
     * @covers ::getConfig
     * @covers ::getCommandArgs
     * @covers ::setCommandArgs
     * @covers ::doClearCache
     * @covers ::setClearCache
     * @covers ::failOnCancel
     * @covers ::setFailOnCancel
     * @covers ::getCancelReason
     * @covers ::setCancelReason
     * @covers ::cancelLoad
     * @covers ::loadCancelled
     */
    public function testGettersAndSetters()
    {
        $additionalArgs = ['bing'];
        $otherArgs = ['blah'];

        $config = MockBaseResponseModel::getConfig();

        $event = new ResponseModelPreLoadEvent($config, $additionalArgs);

        $this->assertEquals($config, $event->getConfig());
        $this->assertEquals($additionalArgs, $event->getCommandArgs());
        $this->assertFalse($event->doClearCache());
        $this->assertFalse($event->failOnCancel());
        $this->assertEquals('', $event->getCancelReason());
        $this->assertFalse($event->loadCancelled());

        $event->setFailOnCancel(true);
        $this->assertTrue($event->failOnCancel());

        $event->setCancelReason('cancel now!');
        $this->assertEquals('cancel now!', $event->getCancelReason());

        $event->setCommandArgs($otherArgs);
        $this->assertSame($otherArgs, $event->getCommandArgs());

        $event->setClearCache(true);
        $this->assertTrue($event->doClearCache());

        $event->cancelLoad(true);
        $this->assertTrue($event->loadCancelled());
    }
}
