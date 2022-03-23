<?php

namespace Cob\Bundle\ApiServicesBundle\Tests\Unit\Models\Config;

use Cob\Bundle\ApiServicesBundle\Models\Config\ResponseModelConfig;
use Cob\Bundle\ApiServicesBundle\Tests\ServiceClientMockTrait;
use Cob\Bundle\ApiServicesBundle\Tests\Unit\Models\Response\BaseResponseModelTestCase;
use Cob\Bundle\ApiServicesBundle\Tests\Unit\Mocks\MockBaseResponseModel;
use Generator;

/**
 * @codeCoverageIgnore
 * @coversDefaultClass \Cob\Bundle\ApiServicesBundle\Models\Config\ResponseModelConfig
 * @covers \Cob\Bundle\ApiServicesBundle\Models\Config\ResponseModelConfigBuilder
 * @covers \Cob\Bundle\ApiServicesBundle\Models\Config\ResponseModelConfigSharedTrait
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Response\BaseResponseModel
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
     * @dataProvider dpTestGettersAndSetters
     * @covers ::__construct
     * @covers ::getCommand
     * @covers ::getDefaultArgs
     * @covers ::getResponseModelClass
     * @covers ::holdsRawData
     * @covers ::getInitCallbacks
     */
    public function testGettersAndSetters(
        bool $holdsRawData,
        bool $expectedHoldsRawData,
        array $initCallbacks,
        array $expectedInitCallbacks
    ) {
        $config = new ResponseModelConfig(
            MockBaseResponseModel::class,
            self::TEST_COMMAND_NAME,
            self::TEST_COMMAND_ARGS,
            $holdsRawData,
            $initCallbacks
        );

        $this->assertEquals(self::TEST_COMMAND_NAME, $config->getCommand());
        $this->assertEquals(self::TEST_COMMAND_ARGS, $config->getDefaultArgs());
        $this->assertEquals(MockBaseResponseModel::class, $config->getResponseModelClass());
        $this->assertEquals($expectedHoldsRawData, $config->holdsRawData());
        $this->assertEquals($expectedInitCallbacks, $config->getInitCallbacks());
    }

    public function dpTestGettersAndSetters(): Generator {
        $callbacks = [
            function () {}
        ];

        yield [false, false, [], []];
        yield [true, true, $callbacks, $callbacks];
    }

    /**
     * @covers ::__construct
     * @covers ::doInits
     * @covers ::getResponseModelClass
     * @covers ::builder
     */
    public function testInits()
    {
        $client = $this->getServiceClientMock([]);
        /**
         * @var MockBaseResponseModel $model
         */
        $model = MockBaseResponseModel::using($client)->withData([]);

        $config = new ResponseModelConfig(
            MockBaseResponseModel::class,
            self::TEST_COMMAND_NAME,
            self::TEST_COMMAND_ARGS,
            false,
            [
                function (MockBaseResponseModel $innerModel) use ($model) {
                    $this->assertSame($model, $innerModel);
                }
            ]
        );

        $config->doInits($model);
    }
}
