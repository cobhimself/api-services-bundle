<?php

namespace Cob\Bundle\ApiServicesBundle\Tests\Unit\Util;

use Cob\Bundle\ApiServicesBundle\Exceptions\InvalidResponseModel;
use Cob\Bundle\ApiServicesBundle\Models\BaseResponseModel;
use Cob\Bundle\ApiServicesBundle\Models\DotData;
use Cob\Bundle\ApiServicesBundle\Models\ResponseModel;
use Cob\Bundle\ApiServicesBundle\Models\Util\ClassUtil;
use Cob\Bundle\ApiServicesBundle\Tests\ServiceClientMockTrait;
use Cob\Bundle\ApiServicesBundle\Tests\Unit\Mocks\MockBaseResponseModel;
use Generator;
use PHPUnit\Framework\TestCase;

/**
 * @codeCoverageIgnore
 * @coversDefaultClass \Cob\Bundle\ApiServicesBundle\Models\Util\ClassUtil
 * @uses \Cob\Bundle\ApiServicesBundle\Models\ResponseModelConfigSharedTrait
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Loader\Config\LoadConfig
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Loader\Config\LoadConfigBuilder
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Loader\Config\LoadConfigSharedTrait
 */
class ClassUtilTest extends TestCase
{
    use ServiceClientMockTrait;

    /**
     * @param $model
     * @param $extends
     * @dataProvider dpTestIsInstanceOf
     *
     * @covers ::isInstanceOf
     * @covers ::className
     */
    public function testIsInstanceOf($model, $extends)
    {
        $this->assertTrue(
            ClassUtil::isInstanceOf(
                $model,
                $extends
            )
        );
    }

    public function dpTestIsInstanceOf(): Generator
    {
        yield [ClassUtilTest::class, TestCase::class];
        yield [BaseResponseModel::class, ResponseModel::class];
        yield [MockBaseResponseModel::class, BaseResponseModel::class];
        yield [MockBaseResponseModel::class, ResponseModel::class];
    }

    /**
     * @param string $expected
     * @param $actual
     * @dataProvider dpTestClassName
     *
     * @covers ::className
     * @uses         \Cob\Bundle\ApiServicesBundle\Models\DotData
     */
    public function testClassName(string $expected, $actual)
    {
        $this->assertEquals($expected, ClassUtil::className($actual));
    }

    public function dpTestClassName(): Generator
    {
        yield [DotData::class, new DotData()];
        yield [DotData::class, DotData::class];
    }

    /**
     * @covers ::isValidResponseModel
     * @covers ::className
     * @covers ::confirmValidResponseModel
     * @covers ::isInstanceOf
     * @uses \Cob\Bundle\ApiServicesBundle\Models\BaseResponseModel
     * @uses \Cob\Bundle\ApiServicesBundle\Models\Deserializer
     * @uses \Cob\Bundle\ApiServicesBundle\Models\DotData
     * @uses \Cob\Bundle\ApiServicesBundle\Models\Loader\AbstractLoader
     * @uses \Cob\Bundle\ApiServicesBundle\Models\Loader\LoadState
     * @uses \Cob\Bundle\ApiServicesBundle\Models\Loader\WithDataLoader
     * @uses \Cob\Bundle\ApiServicesBundle\Models\ResponseModelConfig
     * @uses \Cob\Bundle\ApiServicesBundle\Models\ServiceClient
     */
    public function testIsValidResponseModel()
    {
        $this->assertTrue(ClassUtil::isValidResponseModel(MockBaseResponseModel::class));
        $this->assertTrue(
            ClassUtil::isValidResponseModel(
                MockBaseResponseModel::using($this->getServiceClientMock())->withData([])
            )
        );
    }

    /**
     * @covers ::isValidResponseModel
     * @covers ::className
     * @covers ::confirmValidResponseModel
     * @covers ::isInstanceOf
     * @uses \Cob\Bundle\ApiServicesBundle\Models\DotData
     */
    public function testIsNotValidResponseModel()
    {
        $this->assertFalse(ClassUtil::isValidResponseModel(ClassUtil::class));
        $this->assertFalse(ClassUtil::isValidResponseModel(new DotData()));
    }

    /**
     * @covers ::confirmValidResponseModel
     * @covers \Cob\Bundle\ApiServicesBundle\Exceptions\InvalidResponseModel
     * @covers ::className
     * @covers ::isInstanceOf
     * @covers ::isValidResponseModel
     * @uses \Cob\Bundle\ApiServicesBundle\Models\DotData
     * @covers \Cob\Bundle\ApiServicesBundle\Exceptions\BaseApiServicesBundleException
     */
    public function testConfirmValidResponseModelThrows()
    {
        $this->expectException(InvalidResponseModel::class);
        ClassUtil::confirmValidResponseModel(new DotData());
    }
}