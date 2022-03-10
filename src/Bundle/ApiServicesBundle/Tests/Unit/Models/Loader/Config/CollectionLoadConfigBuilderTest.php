<?php

namespace Cob\Bundle\ApiServicesBundle\Tests\Unit\Models\Loader\Config;

use Cob\Bundle\ApiServicesBundle\Models\Loader\Config\CollectionLoadConfigBuilder;
use Cob\Bundle\ApiServicesBundle\Tests\ServiceClientMockTrait;
use Cob\Bundle\ApiServicesBundle\Tests\Unit\Mocks\PersonCollection;

/**
 * @codeCoverageIgnore
 * @coversDefaultClass \Cob\Bundle\ApiServicesBundle\Models\Loader\Config\CollectionLoadConfigBuilder
 * @covers \Cob\Bundle\ApiServicesBundle\Models\Loader\Config\LoadConfigSharedTrait
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Deserializer
 * @uses \Cob\Bundle\ApiServicesBundle\Models\ServiceClient
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Util\ClassUtil
 * @uses \Cob\Bundle\ApiServicesBundle\Models\BaseResponseModel
 * @uses \Cob\Bundle\ApiServicesBundle\Models\BaseResponseModelCollection
 * @uses \Cob\Bundle\ApiServicesBundle\Models\DotData
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Events\CanGetCollectionLoadConfigTrait
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Events\CanGetCommandTrait
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Events\CanGetResponseTrait
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Events\CanSetCollectionLoadConfigTrait
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\PostAddModelToCollectionEvent
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\PostExecuteCommandEvent
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\PostLoadEvent
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\PreExecuteCommandEvent
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\PreGetLoadCommandEvent
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\PreLoadEvent
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\ResponseModelCollectionEvent
 * @uses \Cob\Bundle\ApiServicesBundle\Models\HasParentTrait
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Loader\AbstractCollectionLoader
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Loader\AbstractLoader
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Loader\Config\LoadConfig
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Loader\Config\LoadConfigBuilder
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Loader\LoadState
 * @uses \Cob\Bundle\ApiServicesBundle\Models\ResponseModelCollectionConfig
 * @uses \Cob\Bundle\ApiServicesBundle\Models\ResponseModelConfig
 * @uses \Cob\Bundle\ApiServicesBundle\Models\ResponseModelConfigSharedTrait
 * @uses \Cob\Bundle\ApiServicesBundle\Models\ResponseModelTrait
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Util\CacheHash
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Util\Promise
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Loader\Config\CollectionLoadConfig
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
     * @uses \Cob\Bundle\ApiServicesBundle\Models\Loader\AsyncCollectionLoader
     * @uses \Cob\Bundle\ApiServicesBundle\Models\Loader\WithDataLoader
     */
    public function testLoadAsync()
    {
        $builder = new CollectionLoadConfigBuilder(
            PersonCollection::class,
            $this->getServiceClientMockWithJsonData([self::PERSON_COLLECTION_JSON])
        );

        /**
         * @var PersonCollection $collection
         */
        $collection = $builder->loadAsync();

        $this->assertInstanceOf(PersonCollection::class, $collection);
        $this->assertTrue($collection->isWaiting());
        $this->assertNotEmpty($collection->dot(''));
        $this->assertTrue($collection->isLoaded());
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