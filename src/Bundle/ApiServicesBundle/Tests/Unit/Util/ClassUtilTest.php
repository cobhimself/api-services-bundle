<?php

namespace Cob\Bundle\ApiServicesBundle\Tests\Unit\Util;

use Cob\Bundle\ApiServicesBundle\Exceptions\IncorrectParentResponseModel;
use Cob\Bundle\ApiServicesBundle\Exceptions\InvalidResponseModel;
use Cob\Bundle\ApiServicesBundle\Models\BaseResponseModel;
use Cob\Bundle\ApiServicesBundle\Models\DotData;
use Cob\Bundle\ApiServicesBundle\Models\ResponseModel;
use Cob\Bundle\ApiServicesBundle\Models\ResponseModelCollection;
use Cob\Bundle\ApiServicesBundle\Models\Util\ClassUtil;
use Cob\Bundle\ApiServicesBundle\Tests\ServiceClientMockTrait;
use Cob\Bundle\ApiServicesBundle\Tests\Unit\Mocks\MockBaseResponseModel;
use Cob\Bundle\ApiServicesBundle\Tests\Unit\Mocks\Person;
use Cob\Bundle\ApiServicesBundle\Tests\Unit\Mocks\PersonCollection;
use Generator;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @codeCoverageIgnore
 * @coversDefaultClass \Cob\Bundle\ApiServicesBundle\Models\Util\ClassUtil
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Config\ResponseModelConfigSharedTrait
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Loader\Config\LoadConfig
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Loader\Config\LoadConfigBuilder
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Loader\Config\LoadConfigSharedTrait
 * @uses \Cob\Bundle\ApiServicesBundle\Models\BaseResponseModelCollection
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Deserializer
 * @uses \Cob\Bundle\ApiServicesBundle\Models\DotData
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Loader\AbstractCollectionLoader
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Loader\Config\CollectionLoadConfig
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Loader\Config\CollectionLoadConfigBuilder
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Loader\LoadState
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Loader\WithDataCollectionLoader
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Config\ResponseModelCollectionConfig
 * @uses \Cob\Bundle\ApiServicesBundle\Models\ResponseModelTrait
 * @uses \Cob\Bundle\ApiServicesBundle\Models\ServiceClient
 * @uses \Cob\Bundle\ApiServicesBundle\Models\BaseResponseModel
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\PostAddModelToCollectionEvent
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\ResponseModelCollectionEvent
 * @uses \Cob\Bundle\ApiServicesBundle\Models\HasParentTrait
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Loader\AbstractLoader
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Loader\WithDataLoader
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Config\ResponseModelConfig
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
     * @uses \Cob\Bundle\ApiServicesBundle\Models\Config\ResponseModelConfig
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
     * @covers ::isValidResponseModelCollection
     * @covers ::className
     * @covers ::isInstanceOf
     * @covers ::confirmValidResponseModelCollection
     * @covers ::confirmValidParentModel
     * @covers ::confirmValidResponseModel
     * @covers ::confirmValidResponseModelOrCollection
     * @covers ::isValidResponseModel
     */
    public function testIsValidResponseModelCollection()
    {
        $this->assertTrue(ClassUtil::isValidResponseModelCollection(
            PersonCollection::class
        ));
        $this->assertTrue(ClassUtil::isValidResponseModelCollection(
            PersonCollection::using($this->getServiceClientMock())->withData([])
        ));
    }

    /**
     * @covers ::confirmValidResponseModelCollection
     * @covers ::isValidResponseModelCollection
     * @covers ::className
     * @covers ::isInstanceOf
     * @uses \Cob\Bundle\ApiServicesBundle\Exceptions\BaseApiServicesBundleException
     * @uses \Cob\Bundle\ApiServicesBundle\Exceptions\InvalidResponseModel
     */
    public function testConfirmValidResponseModelCollection()
    {
        $this->expectException(InvalidResponseModel::class);
        $this->expectExceptionMessage(Person::class . " must implement:\n\t" . ResponseModelCollection::class);

        ClassUtil::confirmValidResponseModelCollection(Person::class);
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

    /**
     * @covers ::confirmValidResponseModelOrCollection
     * @covers ::className
     * @covers ::isInstanceOf
     * @covers ::isValidResponseModel
     * @covers ::isValidResponseModelCollection
     * @covers \Cob\Bundle\ApiServicesBundle\Exceptions\InvalidResponseModel
     * @uses \Cob\Bundle\ApiServicesBundle\Exceptions\BaseApiServicesBundleException
     */
    public function testConfirmValidResponseModelOrCollectionThrows()
    {
        $this->expectException(InvalidResponseModel::class);
        $this->expectExceptionMessage(ClassUtil::class . " must implement one of:\n\t" . ResponseModel::class . "\n\t" . ResponseModelCollection::class);
        ClassUtil::confirmValidResponseModelOrCollection(ClassUtil::class);
    }

    /**
     * @covers ::confirmValidParentModel
     * @covers ::isValidResponseModel
     * @covers ::isValidResponseModelCollection
     * @covers ::className
     * @covers ::isInstanceOf
     * @covers \Cob\Bundle\ApiServicesBundle\Exceptions\IncorrectParentResponseModel
     * @uses \Cob\Bundle\ApiServicesBundle\Exceptions\IncorrectResponseModel
     * @uses \Cob\Bundle\ApiServicesBundle\Exceptions\BaseApiServicesBundleException
     */
    public function testConfirmValidParentModel()
    {
        $this->expectException(IncorrectParentResponseModel::class);
        $this->expectExceptionMessage("Invalid parent model for " . Person::class . ": stdClass is not a valid " . ResponseModel::class . " OR " . ResponseModelCollection::class);
        ClassUtil::confirmValidParentModel(new StdClass(), Person::class);
    }
}
