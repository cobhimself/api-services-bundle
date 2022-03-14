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
use Cob\Bundle\ApiServicesBundle\Models\Loader\Config\CollectionLoadConfigBuilder;
use Cob\Bundle\ApiServicesBundle\Models\ResponseModelCollection;
use Cob\Bundle\ApiServicesBundle\Models\Util\CacheHash;
use Cob\Bundle\ApiServicesBundle\Tests\ServiceClientMockTrait;
use Cob\Bundle\ApiServicesBundle\Tests\Unit\BaseResponseModelTestCase;
use Cob\Bundle\ApiServicesBundle\Tests\Unit\Mocks\BadMockResponseModelCollection;
use Cob\Bundle\ApiServicesBundle\Tests\Unit\Mocks\MockBaseResponseModelWithInit;
use Cob\Bundle\ApiServicesBundle\Tests\Unit\Mocks\Person;
use Cob\Bundle\ApiServicesBundle\Tests\Unit\Mocks\PersonCollection;
use Cob\Bundle\ApiServicesBundle\Tests\Unit\Mocks\PersonCollectionWithCountCapability;
use GuzzleHttp\Psr7\Response;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;

/**
 * @codeCoverageIgnore
 * @coversDefaultClass \Cob\Bundle\ApiServicesBundle\Models\BaseResponseModelCollection
 * @covers ::addResponse
 * @covers ::getConfig
 * @covers ::finalizeChildrenData
 * @covers ::using
 * @covers \Cob\Bundle\ApiServicesBundle\Models\Loader\AbstractCollectionLoader
 * @covers \Cob\Bundle\ApiServicesBundle\Models\ResponseModelCollectionConfig
 * @covers \Cob\Bundle\ApiServicesBundle\Models\Events\CanGetCollectionLoadConfigTrait
 * @covers \Cob\Bundle\ApiServicesBundle\Models\Events\CanSetCollectionLoadConfigTrait
 * @covers \Cob\Bundle\ApiServicesBundle\Models\Events\CanGetCommandTrait
 * @covers \Cob\Bundle\ApiServicesBundle\Models\Events\CanGetResponseTrait
 * @covers \Cob\Bundle\ApiServicesBundle\Models\Loader\Config\CollectionLoadConfig
 * @covers \Cob\Bundle\ApiServicesBundle\Models\Loader\Config\CollectionLoadConfigBuilder
 * @covers \Cob\Bundle\ApiServicesBundle\Models\Loader\Config\LoadConfig
 * @covers \Cob\Bundle\ApiServicesBundle\Models\Loader\Config\LoadConfigBuilder
 * @covers \CoB\Bundle\ApiServicesBundle\Models\Loader\Config\LoadConfigSharedTrait
 * @covers \Cob\Bundle\ApiServicesBundle\Models\ResponseModelTrait
 * @covers \Cob\Bundle\ApiServicesBundle\Models\Loader\AbstractLoader
 * @covers \Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\CommandFulfilledEvent
 * @covers \Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\PostExecuteCommandsEvent
 * @covers \Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\PreExecuteCommandsEvent
 * @covers \Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\PostExecuteCommandEvent
 * @covers \Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\PostLoadEvent
 * @covers \Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\PreExecuteCommandEvent
 * @covers \Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\PreLoadEvent
 * @covers \Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\ResponseModelCollectionEvent
 * @covers \Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\PreGetLoadCommandEvent
 * @covers \Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\PostCountEvent
 * @covers \Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\PostAddModelToCollectionEvent
 * @covers \Cob\Bundle\ApiServicesBundle\Models\BaseResponseModel
 * @covers \Cob\Bundle\ApiServicesBundle\Models\Deserializer
 * @covers \Cob\Bundle\ApiServicesBundle\Models\DotData
 * @covers \Cob\Bundle\ApiServicesBundle\Models\Loader\LoadState
 * @covers \Cob\Bundle\ApiServicesBundle\Models\ResponseModelConfig
 * @covers \Cob\Bundle\ApiServicesBundle\Models\ResponseModelConfigSharedTrait
 * @covers \Cob\Bundle\ApiServicesBundle\Models\Loader\WithDataLoader
 * @covers \Cob\Bundle\ApiServicesBundle\Models\ServiceClient
 * @covers \Cob\Bundle\ApiServicesBundle\Models\Util\ClassUtil
 * @covers \Cob\Bundle\ApiServicesBundle\Models\HasParentTrait
 */
class BaseResponseModelCollectionTest extends BaseResponseModelTestCase
{
    use ServiceClientMockTrait;

    const PERSON_COLLECTION_JSON = __DIR__ . '/../../Resources/MockResponses/personCollection.json';

    /**
     * @covers ::__construct
     * @covers ::withData
     * @covers ::count
     * @covers \Cob\Bundle\ApiServicesBundle\Models\Loader\WithDataCollectionLoader
     */
    public function testWithData()
    {
        $client = $this->getServiceClientMock();
        $data = json_decode($this->getMockResponseDataFromFile(
            self::PERSON_COLLECTION_JSON),
            true
        );

        /**
         * @var PersonCollectionWithCountCapability $mockModel
         */
        $mockModel = PersonCollectionWithCountCapability::using($client)->withData($data['persons']);

        $this->assertTrue($mockModel->isLoadedWithData());
        $this->assertSame($data['persons'], $mockModel->toArray());
        $this->assertCount(4, $mockModel);

        $this->confirmCollectionModels($mockModel);
    }

    /**
     * @covers ::__construct
     * @covers ::load
     * @covers ::isLoaded
     * @covers ::withData
     * @covers ::count
     * @covers \Cob\Bundle\ApiServicesBundle\Models\Loader\Loader
     * @covers \Cob\Bundle\ApiServicesBundle\Models\Loader\AbstractCollectionLoader
     * @covers \Cob\Bundle\ApiServicesBundle\Models\Loader\CollectionLoader
     * @covers \Cob\Bundle\ApiServicesBundle\Models\Util\Promise
     * @covers \Cob\Bundle\ApiServicesBundle\Models\Util\CacheHash
     * @uses \Cob\Bundle\ApiServicesBundle\Models\Loader\WithDataCollectionLoader
     */
    public function testLoad()
    {
        /**
         * @var PersonCollection $mockModel
         */
        $mockModel = PersonCollection::using(
            $this->getServiceClientMockWithJsonData([self::PERSON_COLLECTION_JSON])
        )->load();

        $this->assertTrue($mockModel->isLoaded());

        $this->assertCount(4, $mockModel);
        $this->confirmCollectionModels($mockModel);
    }

    /**
     * @covers ::__construct
     * @covers ::loadAsync
     * @covers ::getConfig
     * @covers ::isLoaded
     * @covers ::isWaiting
     * @covers ::count
     * @covers \Cob\Bundle\ApiServicesBundle\Models\Loader\AbstractCollectionLoader
     * @covers \Cob\Bundle\ApiServicesBundle\Models\Loader\AsyncCollectionLoader
     * @covers \Cob\Bundle\ApiServicesBundle\Models\Util\Promise
     * @covers \Cob\Bundle\ApiServicesBundle\Models\Util\CacheHash
     */
    public function testLoadAsync()
    {
        /**
         * @var PersonCollection $mockModel
         */
        $mockModel = PersonCollection::using(
            $this->getServiceClientMockWithJsonData([self::PERSON_COLLECTION_JSON])
        )->loadAsync();

        $this->assertTrue($mockModel->isWaiting());
        $this->assertCount(4, $mockModel);
        $this->assertTrue($mockModel->isLoaded());
        $this->confirmCollectionModels($mockModel);
    }

    /**
     * @covers ::__construct
     * @covers ::getConfig
     * @covers ::withData
     * @covers \Cob\Bundle\ApiServicesBundle\Models\Loader\AbstractLoader::getNewResponseClass
     * @covers \Cob\Bundle\ApiServicesBundle\Models\ResponseModelConfig::doInits
     * @covers \Cob\Bundle\ApiServicesBundle\Models\Loader\WithDataLoader
     */
    public function testDoInits()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage(MockBaseResponseModelWithInit::EXPECTED_EXCEPTION_MSG);

        MockBaseResponseModelWithInit::using($this->getServiceClientMock([]))->withData([]);
    }

    /**
     * @covers ::setup
     * @covers ::getConfig
     * @covers ::withData
     * @uses \Cob\Bundle\ApiServicesBundle\Exceptions\BaseApiServicesBundleException
     */
    public function testSetupException()
    {
        $this->expectException(ResponseModelSetupException::class);
        BadMockResponseModelCollection::using($this->getServiceClientMock())->withData([]);
    }

    /**
     * @covers ::__construct
     * @covers ::count
     * @covers ::loadAsync
     * @covers ::get
     * @covers \Cob\Bundle\ApiServicesBundle\Models\Loader\AsyncCollectionLoader
     * @covers \Cob\Bundle\ApiServicesBundle\Models\Util\CacheHash::getHashForResponseCollectionClassAndArgs
     * @covers \Cob\Bundle\ApiServicesBundle\Models\Util\CacheHash::hashArray
     * @covers \Cob\Bundle\ApiServicesBundle\Models\Count
     * @covers \Cob\Bundle\ApiServicesBundle\Models\Util\Promise::async
     */
    public function testCanLoadInChunksWithCountCommand()
    {
        $client = $this->getServiceClientMockWithJsonData([
            //Our first response should be the call to the count command
            __DIR__ . '/../../Resources/MockResponses/personCollectionCount.json',
            __DIR__ . '/../../Resources/MockResponses/personCollectionChunk1.json',
            __DIR__ . '/../../Resources/MockResponses/personCollectionChunk2.json'
        ]);

        /**
         * @var PersonCollectionWithCountCapability $mockModel
         */
        $mockModel = PersonCollectionWithCountCapability::using($client)->loadAsync();

        $this->assertTrue($mockModel->isWaiting());
        $this->assertCount(4, $mockModel);
        $this->assertEquals('Person Chunk 1.1', $mockModel->get(0)->getName());
        $this->assertEquals('Person Chunk 1.2', $mockModel->get(1)->getName());
        $this->assertEquals('Person Chunk 2.1', $mockModel->get(2)->getName());
        $this->assertEquals('Person Chunk 2.2', $mockModel->get(3)->getName());
    }

    private function confirmCollectionModels(
        ResponseModelCollection $mockModel
    ) {

        //Our test data is structured in a way that each Person in the collection uses its
        //zero-based index as a part of its details. We can look through our data to confirm
        //our expectations
        foreach($mockModel as $index => $person) {
            /**
             * @var Person $person
             */
            $this->assertInstanceOf(Person::class, $person);
            $this->assertEquals("Person " . ($index + 1), $person->getName());
        }
    }

    /**
     * @covers ::__construct
     * @covers ::getConfig
     * @covers ::isLoaded
     * @covers ::loadAsync
     * @covers ::get
     * @covers \Cob\Bundle\ApiServicesBundle\Models\CacheProvider
     * @covers \Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\ResponseModelPreLoadFromCacheEvent
     * @covers \Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\ResponseModelPostLoadFromCacheEvent
     * @covers \Cob\Bundle\ApiServicesBundle\Models\Loader\AsyncCollectionLoader::load
     * @covers \Cob\Bundle\ApiServicesBundle\Models\Util\CacheHash
     * @covers \Cob\Bundle\ApiServicesBundle\Models\Util\Promise
     * @covers \Cob\Bundle\ApiServicesBundle\Models\Events\CanGetHashTrait
     * @covers \Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\PostLoadFromCacheEvent
     * @covers \Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\PreLoadFromCacheEvent
     */
    public function testLoadFromCache()
    {
        //We'll load up an empty array for data as a response so we can be certain we're obtaining data from cache.
        $client = $this->getServiceClientMockWithResponseData([]);

        $config = PersonCollection::getConfig();
        $hash = CacheHash::getHashForResponseCollectionClassAndArgs(
            $config->getResponseModelClass(),
            $config->getDefaultArgs()
        );

        $data = json_decode(
            $this->getMockResponseDataFromFile(self::PERSON_COLLECTION_JSON),
            true
        );

        /**
         * @var CacheProviderInterface|ObjectProphecy
         */
        $mockCacheProvider = $this->prophesize(CacheProvider::class);
        $mockCacheProvider->contains($hash)->willReturn(true);
        $mockCacheProvider->fetch($hash)->willReturn($data);
        $mockCacheProvider->save(Argument::any(), Argument::any())->shouldNotBeCalled();

        $client->setCacheProvider($mockCacheProvider->reveal());

        /**
         * @var PersonCollection $mockModel
         */
        $mockModel = PersonCollection::using($client)->loadAsync();

        $this->assertFalse($mockModel->isLoaded());
        $this->assertEquals('Person 1', $mockModel->get(0)->getName());
    }

    /**
     * @covers ::__construct
     * @covers ::get
     * @covers ::getConfig
     * @covers ::isLoaded
     * @covers ::loadAsync
     * @covers \Cob\Bundle\ApiServicesBundle\Models\Events\CanGetHashTrait
     * @covers \Cob\Bundle\ApiServicesBundle\Models\CacheProvider
     * @covers \Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\PreLoadFromCacheEvent
     * @covers \Cob\Bundle\ApiServicesBundle\Models\Loader\AsyncCollectionLoader::load
     * @covers \Cob\Bundle\ApiServicesBundle\Models\Util\CacheHash
     * @covers \Cob\Bundle\ApiServicesBundle\Models\Util\Promise
     */
    public function testSaveToCache()
    {
        $data = json_decode(
            $this->getMockResponseDataFromFile(self::PERSON_COLLECTION_JSON),
            true
        );

        $client = $this->getServiceClientMockWithResponseData($data);

        $config = PersonCollection::getConfig();
        $hash = CacheHash::getHashForResponseCollectionClassAndArgs(
            $config->getResponseModelClass(),
            $config->getDefaultArgs()
        );

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
        $mockModel = PersonCollection::using($client)->loadAsync();

        $this->assertFalse($mockModel->isLoaded());
        $this->assertEquals('Person 1', $mockModel->get(0)->getName());
    }

    /**
     * @dataProvider dpTestBadResponsesDuringLoad
     * @covers ::using
     * @covers ::load
     * @covers \Cob\Bundle\ApiServicesBundle\Exceptions\CountDataException
     * @covers \Cob\Bundle\ApiServicesBundle\Models\Count
     * @covers \Cob\Bundle\ApiServicesBundle\Models\ExceptionHandlers\ClientCommandExceptionHandler
     * @covers \Cob\Bundle\ApiServicesBundle\Models\ExceptionHandlers\ResponseModelExceptionHandler
     * @covers \Cob\Bundle\ApiServicesBundle\Exceptions\BaseApiServicesBundleException
     * @covers \Cob\Bundle\ApiServicesBundle\Models\ExceptionHandlers\AbstractExceptionHandler
     * @covers \Cob\Bundle\ApiServicesBundle\Models\Loader\CollectionLoader
     * @covers \Cob\Bundle\ApiServicesBundle\Models\Util\CacheHash
     * @covers \Cob\Bundle\ApiServicesBundle\Models\Util\Promise
     */
    public function testBadResponsesDuringLoad(array $responses, string $responseModel, string $exceptionmessage)
    {
        $this->expectException(ResponseModelException::class);
        $this->expectExceptionMessage($exceptionmessage);

        $client = $this->getServiceClientMock($responses);

        /**
         * @var CollectionLoadConfigBuilder $loadConfigBuilder
         */
        $loadConfigBuilder = call_user_func([$responseModel, 'using'], $client);
        $loadConfigBuilder->load();
    }

    public function dpTestBadResponsesDuringLoad(): \Generator
    {
        yield [
            [new Response(500, [], 'Not found')],
            PersonCollection::class,
            "An exception was thrown during loading"
        ];

        yield [
            [new Response(500, [], 'Not found')],
            PersonCollectionWithCountCapability::class,
            "Could not get count data for"
        ];

        yield [
            [
                new Response(
                    200,
                    [],
                    $this->getMockResponseDataFromFile(
                        __DIR__ . '/../../Resources/MockResponses/personCollectionCount.json'
                    )
                ),
                new Response(500, [], 'Not found')
            ],
            PersonCollectionWithCountCapability::class,
            "There was an issue when running all of the commands."
        ];
    }
}
