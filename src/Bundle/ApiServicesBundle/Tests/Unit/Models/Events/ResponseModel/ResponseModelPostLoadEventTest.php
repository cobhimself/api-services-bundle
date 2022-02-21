<?php

namespace Cob\Bundle\ApiServicesBundle\Tests\Unit\Models\Events\ResponseModel;

use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\ResponseModelPostLoadEvent;
use Cob\Bundle\ApiServicesBundle\Tests\ServiceClientMockTrait;
use Cob\Bundle\ApiServicesBundle\Tests\Unit\BaseResponseModelTestCase;
use Cob\Bundle\ApiServicesBundle\Tests\Unit\Mocks\MockBaseResponseModel;

/**
 * @codeCoverageIgnore
 * @coversDefaultClass \Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\ResponseModelPostLoadEvent
 * @uses \Cob\Bundle\ApiServicesBundle\Models\ServiceClient
 * @uses \Cob\Bundle\ApiServicesBundle\Models\ResponseModelConfig
 * @uses \Cob\Bundle\ApiServicesBundle\Models\BaseResponseModel
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Deserializer
 */
class ResponseModelPostLoadEventTest extends BaseResponseModelTestCase
{
    use ServiceClientMockTrait;

    /**
     * @covers ::__construct
     * @covers \Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\ResponseModelEvent
     * @covers ::getResponseModelConfig
     * @covers ::getCommandArgs
     * @covers ::getResponse
     * @covers ::setResponse
     */
    public function testGettersAndSetters()
    {
        $response = ['test', 'test'];
        $otherResponse = ['blah'];
        $additionalArgs = ['bing'];

        $config = MockBaseResponseModel::getResponseModelConfig();

        $event = new ResponseModelPostLoadEvent(
            $config, $additionalArgs, $response
        );

        $this->assertEquals($config, $event->getResponseModelConfig());
        $this->assertEquals($additionalArgs, $event->getCommandArgs());
        $this->assertSame($response, $event->getResponse());

        $event->setResponse($otherResponse);
        $this->assertSame($otherResponse, $event->getResponse());
    }
}
