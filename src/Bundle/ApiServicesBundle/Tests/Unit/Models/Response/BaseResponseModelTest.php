<?php

/*
 * This file is part of the cobhimself/api-services-bundle package.
 *
 * (c) Collin D. Brooks <collin.brooks@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cob\Bundle\ApiServicesBundle\Tests\Unit\Models\Response;

use Cob\Bundle\ApiServicesBundle\Exceptions\ResponseModelException;
use Cob\Bundle\ApiServicesBundle\Exceptions\ResponseModelSetupException;
use Cob\Bundle\ApiServicesBundle\Models\CacheProvider;
use Cob\Bundle\ApiServicesBundle\Models\CacheProviderInterface;
use Cob\Bundle\ApiServicesBundle\Models\ExceptionHandlers\ResponseModelExceptionHandler;
use Cob\Bundle\ApiServicesBundle\Models\Util\CacheHash;
use Cob\Bundle\ApiServicesBundle\Tests\Unit\Mocks\MockBaseRawDataResponseModel;
use Cob\Bundle\ApiServicesBundle\Tests\Unit\Mocks\MockBaseResponseModel;
use Cob\Bundle\ApiServicesBundle\Tests\Unit\Mocks\MockBaseResponseModelWithInit;
use Cob\Bundle\ApiServicesBundle\Tests\Unit\Mocks\Person;
use Cob\Bundle\ApiServicesBundle\Tests\Unit\Mocks\ResponseModelWithNonExistentProperty;
use Cob\Bundle\ApiServicesBundle\Tests\Unit\Mocks\ResponseModelWithNoSetup;
use Exception;
use GuzzleHttp\Psr7\Response;
use InvalidArgumentException;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;

/**
 * @codeCoverageIgnore
 * @coversDefaultClass \Cob\Bundle\ApiServicesBundle\Models\Response\BaseResponseModel
 *
 * @covers ::__construct
 * @covers ::using
 * @covers \Cob\Bundle\ApiServicesBundle\Exceptions\BaseApiServicesBundleException
 * @covers \Cob\Bundle\ApiServicesBundle\Models\Config\ResponseModelCollectionConfig
 * @covers \Cob\Bundle\ApiServicesBundle\Models\Config\ResponseModelConfig
 * @covers \Cob\Bundle\ApiServicesBundle\Models\Config\ResponseModelConfigBuilder
 * @covers \Cob\Bundle\ApiServicesBundle\Models\Config\ResponseModelConfigSharedTrait
 * @covers \Cob\Bundle\ApiServicesBundle\Models\Deserializer
 * @covers \Cob\Bundle\ApiServicesBundle\Models\DotData
 * @covers \Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\PostAddModelToCollectionEvent
 * @covers \Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\ResponseModelCollectionEvent
 * @covers \Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\ResponseModelEvent
 * @covers \Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\ResponseModelPostExecuteCommandEvent
 * @covers \Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\ResponseModelPostLoadEvent
 * @covers \Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\ResponseModelPreExecuteCommandEvent
 * @covers \Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\ResponseModelPreGetLoadCommandEvent
 * @covers \Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\ResponseModelPreLoadEvent
 * @covers \Cob\Bundle\ApiServicesBundle\Models\Loader\AbstractLoader
 * @covers \Cob\Bundle\ApiServicesBundle\Models\Loader\Config\CollectionLoadConfig
 * @covers \Cob\Bundle\ApiServicesBundle\Models\Loader\Config\CollectionLoadConfigBuilder
 * @covers \Cob\Bundle\ApiServicesBundle\Models\Loader\Config\LoadConfig
 * @covers \Cob\Bundle\ApiServicesBundle\Models\Loader\Config\LoadConfigBuilder
 * @covers \Cob\Bundle\ApiServicesBundle\Models\Loader\Config\LoadConfigSharedTrait
 * @covers \Cob\Bundle\ApiServicesBundle\Models\Loader\LoadState
 * @covers \Cob\Bundle\ApiServicesBundle\Models\Loader\Loader
 * @covers \Cob\Bundle\ApiServicesBundle\Models\Loader\WithDataCollectionLoader
 * @covers \Cob\Bundle\ApiServicesBundle\Models\Loader\WithDataLoader
 * @covers \Cob\Bundle\ApiServicesBundle\Models\Response\Collection\BaseResponseModelCollection
 * @covers \Cob\Bundle\ApiServicesBundle\Models\Response\HasParentTrait
 * @covers \Cob\Bundle\ApiServicesBundle\Models\Response\ResponseModelTrait
 * @covers \Cob\Bundle\ApiServicesBundle\Models\ServiceClient
 * @covers \Cob\Bundle\ApiServicesBundle\Models\Util\ClassUtil
 * @uses \Cob\Bundle\ApiServicesBundle\Models\HasOutputTrait
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Util\LogUtil
 */
class BaseResponseModelTest extends BaseResponseModelTestCase
{
    const PERSON_JSON = __DIR__ . '/../../../Resources/MockResponses/person.json';
    const PERSON_WITH_CHILDREN_JSON = __DIR__ . '/../../../Resources/MockResponses/personWithChildren.json';

    /**
     * @covers ::getConfig
     * @covers ::setup
     * @covers \Cob\Bundle\ApiServicesBundle\Exceptions\ResponseModelSetupException::confirmResponseModelClassSet
     */
    public function testDefaultConfigUsedWhenNoSetupMethod()
    {
        $config = ResponseModelWithNoSetup::getConfig();

        $this->assertEmpty($config->getCommand());
    }

    /**
     * @covers ::withData
     * @covers ::isLoadedWithData
     * @covers ::getConfig
     * @covers ::logLoad
     * @covers \Cob\Bundle\ApiServicesBundle\Models\Loader\WithDataLoader
     * @covers \Cob\Bundle\ApiServicesBundle\Models\Loader\AbstractCollectionLoader
     */
    public function testWithData()
    {
        $client = $this->getServiceClientMock();

        $mockParentModel = $this->getMockParentModel();

        /**
         * @var MockBaseResponseModel $mockModel
         */
        $mockModel = MockBaseResponseModel::using($client)
            ->withParent($mockParentModel)
            ->withData(self::MOCK_RESPONSE_DATA);

        $this->assertTrue($mockModel->isLoadedWithData());
        $this->assertSame(self::MOCK_RESPONSE_DATA, $mockModel->toArray());
        $this->assertEquals(1, $mockModel->dot('data.one'));
        $this->assertTrue($mockModel->hasParent());
        $this->assertEquals($mockParentModel, $mockModel->getParent());
    }

    /**
     * @covers ::withData
     * @covers ::isLoadedWithData
     * @covers ::getConfig
     * @covers ::logLoad
     * @covers \Cob\Bundle\ApiServicesBundle\Models\Loader\Config\LoadConfigBuilder::withDataFromParent
     * @covers \Cob\Bundle\ApiServicesBundle\Models\Loader\WithDataLoader
     * @covers \Cob\Bundle\ApiServicesBundle\Models\Loader\AbstractCollectionLoader
     */
    public function testWithDataFromParent()
    {
        $client = $this->getServiceClientMock();

        $mockParentModel = $this->getMockParentModel(self::MOCK_RESPONSE_DATA);

        /**
         * @var MockBaseResponseModel $mockModel
         */
        $mockModel = MockBaseResponseModel::using($client)
            ->withDataFromParent($mockParentModel, 'data');

        $this->assertTrue($mockModel->isLoadedWithData());
        $this->assertSame(self::MOCK_INNER_RESPONSE_DATA, $mockModel->toArray());
        $this->assertEquals(1, $mockModel->dot('one'));
        $this->assertTrue($mockModel->hasParent());
        $this->assertEquals($mockParentModel, $mockModel->getParent());
    }

    /**
     * @covers ::getConfig
     * @covers ::withData
     * @covers ::logLoad
     */
    public function testWithDataFromParentBadPath()
    {
        $this->expectException(ResponseModelException::class);
        $this->expectExceptionMessage("Could not load data from '" . MockBaseResponseModel::class . "' at path 'bad.dot.path'.");
        $client = $this->getServiceClientMock();

        $mockParentModel = $this->getMockParentModel(self::MOCK_RESPONSE_DATA);

        /**
         * @var MockBaseResponseModel $mockModel
         */
        MockBaseResponseModel::using($client)
            ->withDataFromParent($mockParentModel, 'bad.dot.path');
    }

    /**
     * @covers ::load
     * @covers ::getConfig
     * @covers ::isLoaded
     * @covers ::withData
     * @covers ::logLoad
     * @covers \Cob\Bundle\ApiServicesBundle\Models\Loader\Loader
     * @covers \Cob\Bundle\ApiServicesBundle\Models\Loader\AbstractCollectionLoader
     * @covers \Cob\Bundle\ApiServicesBundle\Models\Util\Promise
     * @uses \Cob\Bundle\ApiServicesBundle\Models\Config\ResponseModelCollectionConfigBuilder
     * @uses \Cob\Bundle\ApiServicesBundle\Exceptions\ResponseModelSetupException
     */
    public function testLoad()
    {
        $mockParentModel = $this->getMockParentModel();
        $client = $this->getServiceClientMockWithJsonData([self::PERSON_JSON]);

        /**
         * @var Person $mockModel
         */
        $mockModel = Person::using($client)->withParent($mockParentModel)->load();

        $this->assertTrue($mockModel->isLoaded());
        $this->assertEquals("Person 1", $mockModel->getName());
        $this->assertEquals(1, $mockModel->getAge());
        $this->assertEmpty($mockModel->getChildren());
        $this->assertEquals($mockParentModel, $mockModel->getParent());
    }

    /**
     * @covers ::getConfig
     * @covers ::loadAsync
     * @covers ::logLoad
     * @covers \Cob\Bundle\ApiServicesBundle\Models\Loader\AsyncLoader::load
     * @covers \Cob\Bundle\ApiServicesBundle\Models\Http\RawResponse
     * @covers \Cob\Bundle\ApiServicesBundle\Models\Util\Promise::async
     * @covers \Cob\Bundle\ApiServicesBundle\Exceptions\ResponseModelSetupException::confirmResponseModelClassSet
     */
    public function testLoadRawData()
    {
        $client = $this->getServiceClientMock(
            [new Response(200, [], self::MOCK_RAW_TEST_DATA)]
        );

        /**
         * @var MockBaseRawDataResponseModel $mock
         */
        $mock = MockBaseRawDataResponseModel::using($client)
            ->loadAsync();

        $this->assertEquals(self::MOCK_RAW_TEST_DATA, $mock->getRawData());
    }

    /**
     * @covers ::withRawData
     * @covers ::getConfig
     * @covers ::logLoad
     * @covers \Cob\Bundle\ApiServicesBundle\Exceptions\ResponseModelSetupException::confirmResponseModelClassSet
     * @covers \Cob\Bundle\ApiServicesBundle\Models\Loader\WithRawDataLoader::load
     */
    public function testGetDataFromRawModelNotAllowed()
    {
        $this->expectException(ResponseModelException::class);
        $this->expectExceptionMessage('holds raw data');

        $client = $this->getServiceClientMock();

        $mock = MockBaseRawDataResponseModel::using($client)->withRawData('blah');
        $mock->dot('will not work');
    }

    /**
     * @covers ::withData
     * @covers ::getConfig
     * @covers ::logLoad
     * @covers \Cob\Bundle\ApiServicesBundle\Exceptions\ResponseModelSetupException
     */
    public function testCannotGetRawDataFromNormalResponse()
    {
        $this->expectException(ResponseModelException::class);
        $this->expectExceptionMessage('holds structured data');

        $client = $this->getServiceClientMock();

        Person::using($client)->withData([])->getRawData();
    }

    /**
     * @covers ::loadAsync
     * @covers ::getConfig
     * @covers ::isLoaded
     * @covers ::isWaiting
     * @covers ::withData
     * @covers ::logLoad
     * @covers \Cob\Bundle\ApiServicesBundle\Models\Loader\AbstractCollectionLoader
     * @covers \Cob\Bundle\ApiServicesBundle\Models\Loader\AsyncLoader
     * @covers \Cob\Bundle\ApiServicesBundle\Models\Util\Promise
     */
    public function testLoadAsync()
    {
        $mockParentModel = $this->getMockParentModel();
        $client = $this->getServiceClientMockWithJsonData([self::PERSON_JSON]);

        /**
         * @var Person $mockModel
         */
        $mockModel = Person::using($client)->withParent($mockParentModel)->loadAsync();

        $this->assertTrue($mockModel->isWaiting());
        $this->assertEquals("Person 1", $mockModel->getName());
        //Now that we've attempted to get a value, we should be loaded
        $this->assertTrue($mockModel->isLoaded());
        $this->assertEquals(1, $mockModel->getAge());
        $this->assertEmpty($mockModel->getChildren());

        $this->assertEquals($mockParentModel, $mockModel->getParent());
    }

    /**
     * @covers ::getConfig
     * @covers ::withData
     * @covers ::logLoad
     * @covers \Cob\Bundle\ApiServicesBundle\Models\Config\ResponseModelConfig::doInits
     * @covers \Cob\Bundle\ApiServicesBundle\Models\Loader\WithDataLoader
     * @covers \Cob\Bundle\ApiServicesBundle\Exceptions\ResponseModelSetupException::confirmResponseModelClassSet
     */
    public function testDoInits()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage(MockBaseResponseModelWithInit::EXPECTED_EXCEPTION_MSG);
        $client = $this->getServiceClientMock([]);

        MockBaseResponseModelWithInit::using($client)->withData([]);
    }

    /**
     * @covers ::load
     * @covers ::getConfig
     * @covers ::isLoaded
     * @covers ::withData
     * @covers ::logLoad
     * @uses \Cob\Bundle\ApiServicesBundle\Models\Loader\AbstractCollectionLoader
     * @uses \Cob\Bundle\ApiServicesBundle\Models\Loader\WithDataLoader
     * @uses \Cob\Bundle\ApiServicesBundle\Models\Util\Promise
     */
    public function testChildCollectionSetCorrectly()
    {
        $client = $this->getServiceClientMockWithJsonData([self::PERSON_WITH_CHILDREN_JSON]);

        /**
         * @var Person $mockModel
         */
        $mockModel = Person::using($client)->load();

        $this->assertTrue($mockModel->isLoaded());
        $this->assertEquals("Person 1", $mockModel->getName());
        //Now that we've attempted to get a value, we should be loaded
        $this->assertEquals(1, $mockModel->getAge());
        $this->assertCount(2, $mockModel->getChildren());

        /**
         * @var Person $child1
         */
        $child1 = $mockModel->getChildren()->get(0);

        $this->assertEquals('Person 1.1', $child1->getName());
        $this->assertEquals(11, $child1->getAge());
        $this->assertTrue($child1->isAlive());

        /**
         * @var Person $child2
         */
        $child2 = $mockModel->getChildren()->get(1);
        $this->assertEquals('Person 1.2', $child2->getName());
        $this->assertEquals(12, $child2->getAge());
        $this->assertFalse($child2->isAlive());
    }

    /**
     * @covers ::getConfig
     * @covers ::isLoaded
     * @covers ::loadAsync
     * @covers ::logLoad
     * @covers \Cob\Bundle\ApiServicesBundle\Models\CacheProvider
     * @covers \Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\ResponseModelPreLoadFromCacheEvent
     * @covers \Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\ResponseModelPostLoadFromCacheEvent
     * @covers \Cob\Bundle\ApiServicesBundle\Models\Loader\AsyncLoader::load
     * @covers \Cob\Bundle\ApiServicesBundle\Models\Util\CacheHash
     * @covers \Cob\Bundle\ApiServicesBundle\Models\Util\Promise
     */
    public function testLoadFromCache()
    {
        //We won't have any response data set here so we can confirm the data is being loaded from cache.
        $client = $this->getServiceClientMockWithResponseData([]);

        $config = Person::getConfig();
        $hash = CacheHash::getHashForResponseClassAndArgs($config->getResponseModelClass(), $config->getDefaultArgs());

        $data = (array) json_decode($this->getMockResponseDataFromFile(self::PERSON_JSON));

        /**
         * @var CacheProviderInterface|ObjectProphecy
         */
        $mockCacheProvider = $this->prophesize(CacheProvider::class);
        /** @noinspection PhpUndefinedMethodInspection */
        $mockCacheProvider->contains($hash)->willReturn(true);
        /** @noinspection PhpUndefinedMethodInspection */
        $mockCacheProvider->fetch($hash)->willReturn($data);
        /** @noinspection PhpUndefinedMethodInspection */
        $mockCacheProvider->save(Argument::any(), Argument::any())->shouldNotBeCalled();

        /** @noinspection PhpParamsInspection */
        $client->setCacheProvider($mockCacheProvider->reveal());

        /**
         * @var Person $mockModel
         */
        $mockModel = Person::using($client)->loadAsync();

        $this->assertFalse($mockModel->isLoaded());
        $this->assertEquals('Person 1', $mockModel->getName());
    }

    /**
     * @covers ::getConfig
     * @covers ::isLoaded
     * @covers ::loadAsync
     * @covers ::logLoad
     * @covers \Cob\Bundle\ApiServicesBundle\Models\CacheProvider
     * @covers \Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\ResponseModelPreLoadFromCacheEvent
     * @covers \Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\ResponseModelPostLoadFromCacheEvent
     * @covers \Cob\Bundle\ApiServicesBundle\Models\Loader\AsyncLoader::load
     * @covers \Cob\Bundle\ApiServicesBundle\Models\Util\CacheHash
     * @covers \Cob\Bundle\ApiServicesBundle\Models\Util\Promise
     */
    public function testSaveToCache()
    {
        $data = (array) json_decode($this->getMockResponseDataFromFile(self::PERSON_JSON));
        $client = $this->getServiceClientMockWithResponseData($data);

        $config = Person::getConfig();
        $hash = CacheHash::getHashForResponseClassAndArgs($config->getResponseModelClass(), $config->getDefaultArgs());

        /**
         * @var CacheProviderInterface|ObjectProphecy
         */
        $mockCacheProvider = $this->prophesize(CacheProvider::class);
        /** @noinspection PhpUndefinedMethodInspection */
        $mockCacheProvider->contains($hash)->willReturn(false);
        /** @noinspection PhpUndefinedMethodInspection */
        $mockCacheProvider->save($hash, $data)->willReturn(true);

        /** @noinspection PhpParamsInspection */
        $client->setCacheProvider($mockCacheProvider->reveal());

        /**
         * @var Person $mockModel
         */
        $mockModel = Person::using($client)->loadAsync();

        $this->assertFalse($mockModel->isLoaded());
        $this->assertEquals('Person 1', $mockModel->getName());
    }

    /**
     * @covers ::using
     * @covers ::getConfig
     * @covers ::load
     * @covers ::logLoad
     * @covers \Cob\Bundle\ApiServicesBundle\Models\ExceptionHandlers\AbstractExceptionHandler
     * @covers \Cob\Bundle\ApiServicesBundle\Models\Util\Promise
     *
     */
    public function testBadResponsesDuringLoad()
    {
        $this->expectException(ResponseModelException::class);
        $this->expectExceptionMessage("An exception was thrown while loading");

        $client = $this->getServiceClientMock(
            [new Response(500, [], 'Not found')]
        );

        Person::using($client)->load();
    }

    /**
     * @covers ::using
     * @covers ::getConfig
     * @covers ::load
     * @covers ::logLoad
     * @covers \Cob\Bundle\ApiServicesBundle\Models\ExceptionHandlers\AbstractExceptionHandler
     * @covers \Cob\Bundle\ApiServicesBundle\Models\Util\Promise
     *
     */
    public function testBadResponsesDuringLoadWithCustomHandler()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Custom error message");

        $client = $this->getServiceClientMock(
            [new Response(500, [], 'Not found')]
        );

        $exceptionCode = null;

        Person::using($client)
            ->handleExceptionsWith(
                ResponseModelExceptionHandler::passThruAndWrapWith(
                    InvalidArgumentException::class,
                    ['Custom error message', $exceptionCode]
                )
            )->load();
    }

    /**
     * @covers ::getConfig
     * @covers ::withRawData
     * @covers ::logLoad
     * @covers \Cob\Bundle\ApiServicesBundle\Models\Loader\WithRawDataLoader
     */
    public function testResponseModelWithRawData()
    {
        $rawData = 'this is raw data';
        $client = $this->getServiceClientMock();

        /**
         * @var MockBaseRawDataResponseModel $model
         */
        $model = MockBaseRawDataResponseModel::using($client)->withRawData($rawData);

        $this->assertEquals($rawData, $model->getRawData());
    }

    /**
     * @covers ::getConfig
     * @covers ::setup
     * @covers ::withData
     * @covers ::logLoad
     * @covers \Cob\Bundle\ApiServicesBundle\Exceptions\ResponseModelSetupException::confirmResponseModelClassSet
     */
    public function testCheckForPropertyExceptionThrowsException()
    {
        $this->expectException(ResponseModelSetupException::class);
        $this->expectExceptionMessage('Could not get property');
        /**
         * @var ResponseModelWithNonExistentProperty $model
         */
        $model = ResponseModelWithNonExistentProperty::using($this->getServiceClientMock())
            ->withData([]);

        $model->getNonExistentProperty();
    }
}
