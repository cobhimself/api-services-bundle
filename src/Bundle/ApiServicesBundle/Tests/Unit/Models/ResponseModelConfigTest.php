<?php

namespace Cob\Bundle\ApiServicesBundle\Tests\Unit\Models;

use Cob\Bundle\ApiServicesBundle\Models\ResponseModelConfig;
use Cob\Bundle\ApiServicesBundle\Tests\ServiceClientMockTrait;
use Cob\Bundle\ApiServicesBundle\Tests\Unit\BaseResponseModelTestCase;
use Cob\Bundle\ApiServicesBundle\Tests\Unit\Mocks\MockBaseResponseModel;

/**
 * @codeCoverageIgnore
 * @coversDefaultClass \Cob\Bundle\ApiServicesBundle\Models\ResponseModelConfig
 * @uses \Cob\Bundle\ApiServicesBundle\Models\BaseResponseModel
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Deserializer
 * @uses \Cob\Bundle\ApiServicesBundle\Models\DotData
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Loader\AbstractLoader
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Loader\LoadState
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Loader\WithDataLoader
 * @uses \Cob\Bundle\ApiServicesBundle\Models\ServiceClient
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Util\ClassUtil
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Loader\Config\LoadConfig
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Loader\Config\LoadConfigBuilder
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Loader\Config\LoadConfigSharedTrait
 */
class ResponseModelConfigTest extends BaseResponseModelTestCase
{
    use ServiceClientMockTrait;

    /**
     * @covers ::__construct
     * @covers ::setResponseModelClass
     * @covers ::getCommand
     * @covers ::getDefaultArgs
     * @covers ::getResponseModelClass
     * @covers ::holdsRawData
     * @covers ::setHoldsRawData
     */
    public function testGettersAndSetters()
    {
        $config = new ResponseModelConfig(self::TEST_COMMAND_NAME, self::TEST_COMMAND_ARGS);
        $config->setResponseModelClass(MockBaseResponseModel::class);

        $this->assertEquals(self::TEST_COMMAND_NAME, $config->getCommand());
        $this->assertEquals(self::TEST_COMMAND_ARGS, $config->getDefaultArgs());
        $this->assertEquals(MockBaseResponseModel::class, $config->getResponseModelClass());
        $this->assertFalse($config->holdsRawData());

        $config->setHoldsRawData(true);
        $this->assertTrue($config->holdsRawData());
    }

    /**
     * @covers ::__construct
     * @covers ::doInits
     * @covers ::addInitCallback
     * @covers ::getResponseModelClass
     */
    public function testInits()
    {
        $client = $this->getServiceClientMock([]);
        /**
         * @var MockBaseResponseModel $model
         */
        $model = MockBaseResponseModel::using($client)->withData([]);

        $config = new ResponseModelConfig(self::TEST_COMMAND_NAME, self::TEST_COMMAND_ARGS);
        $config->addInitCallback(function (MockBaseResponseModel $innerModel) use ($model) {
            $this->assertSame($model, $innerModel);
        });

        $config->doInits($model);
    }
}
