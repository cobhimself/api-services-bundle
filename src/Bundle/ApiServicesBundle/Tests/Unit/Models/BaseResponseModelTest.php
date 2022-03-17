<?php

/*
 * This file is part of the cobhimself/api-services-bundle package.
 *
 * (c) Collin D. Brooks <collin.brooks@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cob\Bundle\ApiServicesBundle\Tests\Unit\Models;

use Cob\Bundle\ApiServicesBundle\Exceptions\ResponseModelException;
use Cob\Bundle\ApiServicesBundle\Exceptions\ResponseModelSetupException;
use Cob\Bundle\ApiServicesBundle\Models\CacheProvider;
use Cob\Bundle\ApiServicesBundle\Models\CacheProviderInterface;
use Cob\Bundle\ApiServicesBundle\Models\Util\CacheHash;
use Cob\Bundle\ApiServicesBundle\Tests\Unit\BaseResponseModelTestCase;
use Cob\Bundle\ApiServicesBundle\Tests\Unit\Mocks\BadMockResponseModel;
use Cob\Bundle\ApiServicesBundle\Tests\Unit\Mocks\MockBaseRawDataResponseModel;
use Cob\Bundle\ApiServicesBundle\Tests\Unit\Mocks\MockBaseResponseModel;
use Cob\Bundle\ApiServicesBundle\Tests\Unit\Mocks\MockBaseResponseModelWithInit;
use Cob\Bundle\ApiServicesBundle\Tests\Unit\Mocks\Person;
use GuzzleHttp\Psr7\Response;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;

/**
 * @codeCoverageIgnore
 * @coversDefaultClass \Cob\Bundle\ApiServicesBundle\Models\BaseResponseModel
 *
 * @covers ::__construct
 * @covers ::using
 * @covers \Cob\Bundle\ApiServicesBundle\Models\Loader\AbstractLoader
 * @covers \Cob\Bundle\ApiServicesBundle\Models\Loader\Config\LoadConfig
 * @covers \Cob\Bundle\ApiServicesBundle\Models\Loader\Config\LoadConfigBuilder
 * @covers \Cob\Bundle\ApiServicesBundle\Models\Loader\Config\LoadConfigSharedTrait
 * @covers \Cob\Bundle\ApiServicesBundle\Models\Loader\Config\CollectionLoadConfig
 * @covers \Cob\Bundle\ApiServicesBundle\Models\Loader\Config\CollectionLoadConfigBuilder
 * @covers \Cob\Bundle\ApiServicesBundle\Models\ResponseModelTrait
 * @covers \Cob\Bundle\ApiServicesBundle\Models\ServiceClient
 * @covers \Cob\Bundle\ApiServicesBundle\Exceptions\BaseApiServicesBundleException
 * @covers \Cob\Bundle\ApiServicesBundle\Models\ResponseModelConfig
 * @covers \Cob\Bundle\ApiServicesBundle\Models\Util\ClassUtil
 * @covers \Cob\Bundle\ApiServicesBundle\Models\Deserializer
 * @covers \Cob\Bundle\ApiServicesBundle\Models\DotData
 * @covers \Cob\Bundle\ApiServicesBundle\Models\Loader\Loader
 * @covers \Cob\Bundle\ApiServicesBundle\Models\Loader\LoadState
 * @covers \Cob\Bundle\ApiServicesBundle\Models\BaseResponseModelCollection
 * @covers \Cob\Bundle\ApiServicesBundle\Models\ResponseModelConfigSharedTrait
 * @covers \Cob\Bundle\ApiServicesBundle\Models\Loader\AbstractLoader
 * @covers \Cob\Bundle\ApiServicesBundle\Models\Loader\WithDataCollectionLoader
 * @covers \Cob\Bundle\ApiServicesBundle\Models\Loader\WithDataLoader
 * @covers \Cob\Bundle\ApiServicesBundle\Models\ResponseModelCollectionConfig
 * @covers \Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\ResponseModelEvent
 * @covers \Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\ResponseModelPostExecuteCommandEvent
 * @covers \Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\ResponseModelPostLoadEvent
 * @covers \Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\ResponseModelPreExecuteCommandEvent
 * @covers \Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\ResponseModelPreLoadEvent
 * @covers \Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\ResponseModelPreGetLoadCommandEvent
 * @covers \Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\PostAddModelToCollectionEvent
 * @covers \Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\ResponseModelCollectionEvent
 * @covers \Cob\Bundle\ApiServicesBundle\Models\HasParentTrait
 */
class BaseResponseModelTest extends BaseResponseModelTestCase
{
    const PERSON_JSON = __DIR__ . '/../../Resources/MockResponses/person.json';
    const PERSON_WITH_CHILDREN_JSON = __DIR__ . '/../../Resources/MockResponses/personWithChildren.json';

    /**
     * @covers ::withData
     * @covers ::isLoadedWithData
     * @covers ::getConfig
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
     * @covers ::load
     * @covers ::getConfig
     * @covers ::isLoaded
     * @covers ::withData
     * @covers \Cob\Bundle\ApiServicesBundle\Models\Loader\Loader
     * @covers \Cob\Bundle\ApiServicesBundle\Models\Loader\AbstractCollectionLoader
     * @covers \Cob\Bundle\ApiServicesBundle\Models\Util\Promise
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
     * @covers ::loadAsync
     * @covers ::getConfig
     * @covers ::isLoaded
     * @covers ::isWaiting
     * @covers ::withData
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
     * @covers \Cob\Bundle\ApiServicesBundle\Models\ResponseModelConfig::doInits
     * @covers \Cob\Bundle\ApiServicesBundle\Models\Loader\WithDataLoader
     */
    public function testDoInits()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage(MockBaseResponseModelWithInit::EXPECTED_EXCEPTION_MSG);
        $client = $this->getServiceClientMock([]);

        MockBaseResponseModelWithInit::using($client)->withData([]);
    }

    /**
     * @covers ::setup
     * @covers ::getConfig
     * @covers ::withData
     */
    public function testSetupException()
    {
        $client = $this->getServiceClientMock();
        $this->expectException(ResponseModelSetupException::class);

        BadMockResponseModel::using($client)->withData([]);
    }

    /**
     * @covers ::load
     * @covers ::getConfig
     * @covers ::isLoaded
     * @covers ::withData
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
        $mockCacheProvider->contains($hash)->willReturn(true);
        $mockCacheProvider->fetch($hash)->willReturn($data);
        $mockCacheProvider->save(Argument::any(), Argument::any())->shouldNotBeCalled();

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
        $mockCacheProvider->contains($hash)->willReturn(false);
        $mockCacheProvider->save($hash, $data)->willReturn(true);

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
     * @covers \Cob\Bundle\ApiServicesBundle\Models\ExceptionHandlers\AbstractExceptionHandler
     * @covers \Cob\Bundle\ApiServicesBundle\Models\Util\Promise
     *
     */
    public function testBadResponsesDuringLoad()
    {
        $this->expectException(ResponseModelException::class);
        $this->expectExceptionMessage("An exception was thrown during loading");

        $client = $this->getServiceClientMock(
            [new Response(500, [], 'Not found')]
        );

        Person::using($client)->load();
    }

    /**
     * @covers ::getConfig
     * @covers ::withRawData
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
}
