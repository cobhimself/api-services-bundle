<?php

namespace Cob\Bundle\ApiServicesBundle\Tests\Unit\Models\Loader\Config;

use Cob\Bundle\ApiServicesBundle\Models\ExceptionHandlers\ResponseModelExceptionHandler;
use Cob\Bundle\ApiServicesBundle\Models\Loader\Config\CollectionLoadConfigBuilder;
use Cob\Bundle\ApiServicesBundle\Tests\ServiceClientMockTrait;
use Cob\Bundle\ApiServicesBundle\Tests\Unit\Mocks\Person;
use Cob\Bundle\ApiServicesBundle\Tests\Unit\Mocks\PersonCollection;

/**
 * @codeCoverageIgnore
 * @coversDefaultClass \Cob\Bundle\ApiServicesBundle\Models\Loader\Config\CollectionLoadConfigBuilder
 * @covers \Cob\Bundle\ApiServicesBundle\Models\Loader\Config\LoadConfigSharedTrait
 * @covers \Cob\Bundle\ApiServicesBundle\Models\Deserializer
 * @covers \Cob\Bundle\ApiServicesBundle\Models\ServiceClient
 * @covers \Cob\Bundle\ApiServicesBundle\Models\Util\ClassUtil
 * @covers \Cob\Bundle\ApiServicesBundle\Models\Response\BaseResponseModel
 * @covers \Cob\Bundle\ApiServicesBundle\Models\Response\Collection\BaseResponseModelCollection
 * @covers \Cob\Bundle\ApiServicesBundle\Models\DotData
 * @covers \Cob\Bundle\ApiServicesBundle\Models\Events\CanGetCollectionLoadConfigTrait
 * @covers \Cob\Bundle\ApiServicesBundle\Models\Events\CanGetCommandTrait
 * @covers \Cob\Bundle\ApiServicesBundle\Models\Events\CanGetResponseTrait
 * @covers \Cob\Bundle\ApiServicesBundle\Models\Events\CanSetCollectionLoadConfigTrait
 * @covers \Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\PostAddModelToCollectionEvent
 * @covers \Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\PostExecuteCommandEvent
 * @covers \Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\PostLoadEvent
 * @covers \Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\PreExecuteCommandEvent
 * @covers \Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\PreGetLoadCommandEvent
 * @covers \Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\PreLoadEvent
 * @covers \Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\ResponseModelCollectionEvent
 * @covers \Cob\Bundle\ApiServicesBundle\Models\Response\HasParentTrait
 * @covers \Cob\Bundle\ApiServicesBundle\Models\Loader\AbstractCollectionLoader
 * @covers \Cob\Bundle\ApiServicesBundle\Models\Loader\AbstractLoader
 * @covers \Cob\Bundle\ApiServicesBundle\Models\Loader\Config\LoadConfig
 * @covers \Cob\Bundle\ApiServicesBundle\Models\Loader\Config\LoadConfigBuilder
 * @covers \Cob\Bundle\ApiServicesBundle\Models\Loader\LoadState
 * @covers \Cob\Bundle\ApiServicesBundle\Models\Config\ResponseModelCollectionConfig
 * @covers \Cob\Bundle\ApiServicesBundle\Models\Config\ResponseModelConfig
 * @covers \Cob\Bundle\ApiServicesBundle\Models\Config\ResponseModelConfigSharedTrait
 * @covers \Cob\Bundle\ApiServicesBundle\Models\Response\ResponseModelTrait
 * @covers \Cob\Bundle\ApiServicesBundle\Models\Util\CacheHash
 * @covers \Cob\Bundle\ApiServicesBundle\Models\Util\Promise
 * @covers \Cob\Bundle\ApiServicesBundle\Models\Loader\Config\CollectionLoadConfig
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Config\ResponseModelCollectionConfigBuilder
 */
class CollectionLoadConfigBuilderTest extends CollectionLoadConfigTestCase
{
    use ServiceClientMockTrait;

    const PERSON_COLLECTION_JSON = __DIR__ . '/../../../../Resources/MockResponses/personCollection.json';

    /**
     * @covers ::__construct
     * @covers ::build
     * @covers ::existingData
     * @covers ::validateModelClass
     * @covers ::clearCache
     * @covers ::handleExceptionsWith
     * @covers ::withCommandArgs
     * @covers ::withCountCommandArgs
     * @covers ::withParent
     * @covers \Cob\Bundle\ApiServicesBundle\Models\Loader\Config\CollectionLoadConfig
     * @uses \Cob\Bundle\ApiServicesBundle\Models\ExceptionHandlers\AbstractExceptionHandler
     *
     * @param array $actual
     * @param array $expected
     *
     * @dataProvider dpTestGetters
     * @noinspection DuplicatedCode
     */
    public function testGetters(array $actual, array $expected) {
        list(
            $actualClient,
            $actualCommandArgs,
            $actualCountCommandArgs,
            $actualParent,
            $actualClearCache,
            $actualHandler,
            $actualExistingData
        ) = $actual;

        $builder = new CollectionLoadConfigBuilder(PersonCollection::class, $actualClient);

        if (!is_null($actualCommandArgs)) {
            $builder->withCommandArgs($actualCommandArgs);
        }

        if (!is_null($actualCountCommandArgs)) {
            $builder->withCountCommandArgs($actualCountCommandArgs);
        }

        if (!is_null($actualParent)) {
            $builder->withParent($actualParent);
        }

        if (!is_null($actualClearCache)) {
            $builder->clearCache($actualClearCache);
        }

        if (!is_null($actualHandler)) {
            $builder->handleExceptionsWith($actualHandler);
        }

        if (!is_null($actualExistingData)) {
            $builder->existingData($actualExistingData);
        }

        $loadConfig = $builder->build();

        $this->confirmLoadConfigAssertions($expected, $loadConfig);
    }

    /**
     * @covers ::__construct
     * @covers ::build
     * @covers ::load
     * @covers ::provide
     * @covers ::validateModelClass
     * @uses \Cob\Bundle\ApiServicesBundle\Models\Loader\CollectionLoader
     * @uses \Cob\Bundle\ApiServicesBundle\Models\Loader\WithDataLoader
     */
    public function testLoad()
    {
        $builder = new CollectionLoadConfigBuilder(
            PersonCollection::class,
            $this->getServiceClientMockWithJsonData([self::PERSON_COLLECTION_JSON])
        );

        /**
         * @var PersonCollection $collection
         */
        $collection = $builder->load();

        $this->assertInstanceOf(PersonCollection::class, $collection);
        $this->assertTrue($collection->isLoaded());
        $this->assertNotEmpty($collection->dot(''));
    }

    /**
     * @covers ::__construct
     * @covers ::build
     * @covers ::loadAsync
     * @covers ::provide
     * @covers ::validateModelClass
     * @covers ::withParent
     * @uses \Cob\Bundle\ApiServicesBundle\Models\Loader\AsyncCollectionLoader
     * @uses \Cob\Bundle\ApiServicesBundle\Models\Loader\WithDataLoader
     */
    public function testLoadAsync()
    {
        $builder = new CollectionLoadConfigBuilder(
            PersonCollection::class,
            $this->getServiceClientMockWithJsonData([self::PERSON_COLLECTION_JSON])
        );

        //Add a parent to confirm it's set in our constructor
        $builder->withParent(Person::using($this->getServiceClientMock())->withData([]));

        /**
         * @var PersonCollection $collection
         */
        $collection = $builder->loadAsync();

        $this->assertInstanceOf(PersonCollection::class, $collection);
        $this->assertTrue($collection->isWaiting());
        $this->assertNotEmpty($collection->dot(''));
        $this->assertTrue($collection->isLoaded());
        $this->assertInstanceOf(Person::class, $collection->getParent());
    }

    /**
     * @covers ::__construct
     * @covers ::build
     * @covers ::withData
     * @covers ::provide
     * @covers ::validateModelClass
     * @uses \Cob\Bundle\ApiServicesBundle\Models\Loader\WithDataCollectionLoader
     * @uses \Cob\Bundle\ApiServicesBundle\Models\Loader\WithDataLoader
     */
    public function testWithData()
    {
        $builder = new CollectionLoadConfigBuilder(
            PersonCollection::class,
            $this->getServiceClientMock([])
        );

        $data = json_decode($this->getMockResponseDataFromFile(
            self::PERSON_COLLECTION_JSON),
            true
        );

        /**
         * @var PersonCollection $collection
         */
        $collection = $builder->withData($data['persons']);

        $this->assertInstanceOf(PersonCollection::class, $collection);
        $this->assertTrue($collection->isLoadedWithData());
        $this->assertNotEmpty($collection->dot(''));
    }
}
