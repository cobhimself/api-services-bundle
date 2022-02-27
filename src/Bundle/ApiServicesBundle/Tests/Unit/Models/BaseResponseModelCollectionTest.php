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

use Cob\Bundle\ApiServicesBundle\Exceptions\ResponseModelSetupException;
use Cob\Bundle\ApiServicesBundle\Models\ResponseModelCollection;
use Cob\Bundle\ApiServicesBundle\Tests\ServiceClientMockTrait;
use Cob\Bundle\ApiServicesBundle\Tests\Unit\BaseResponseModelTestCase;
use Cob\Bundle\ApiServicesBundle\Tests\Unit\Mocks\BadMockResponseModel;
use Cob\Bundle\ApiServicesBundle\Tests\Unit\Mocks\BadMockResponseModelCollection;
use Cob\Bundle\ApiServicesBundle\Tests\Unit\Mocks\MockBaseResponseModelWithInit;
use Cob\Bundle\ApiServicesBundle\Tests\Unit\Mocks\Person;
use Cob\Bundle\ApiServicesBundle\Tests\Unit\Mocks\PersonCollection;
use Cob\Bundle\ApiServicesBundle\Tests\Unit\Mocks\PersonCollectionWithCountCapability;

/**
 * @codeCoverageIgnore
 * @coversDefaultClass \Cob\Bundle\ApiServicesBundle\Models\BaseResponseModelCollection
 * @covers ::addResponse
 * @covers ::getConfig
 * @covers \Cob\Bundle\ApiServicesBundle\Models\Loader\AbstractCollectionLoader
 * @covers \Cob\Bundle\ApiServicesBundle\Models\ResponseModelCollectionConfig
 * @covers \Cob\Bundle\ApiServicesBundle\Models\Events\CanGetCommandArgsTrait
 * @covers \Cob\Bundle\ApiServicesBundle\Models\Events\CanGetCommandTrait
 * @covers \Cob\Bundle\ApiServicesBundle\Models\Events\CanGetResponseTrait
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\CommandFulfilledEvent
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\PostExecuteCommandsEvent
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\PreExecuteCommandsEvent
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\PostExecuteCommandEvent
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\PostLoadEvent
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\PreExecuteCommandEvent
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\PreLoadEvent
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\ResponseModelCollectionEvent
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\ResponseModelCollectionPreGetLoadCommandEvent
 * @uses \Cob\Bundle\ApiServicesBundle\Models\BaseResponseModel
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Deserializer
 * @uses \Cob\Bundle\ApiServicesBundle\Models\DotData
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Loader\AbstractLoader
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Loader\State\LoadState
 * @uses \Cob\Bundle\ApiServicesBundle\Models\ResponseModelConfig
 * @uses \Cob\Bundle\ApiServicesBundle\Models\ResponseModelConfigSharedTrait
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Loader\WithDataLoader
 * @uses \Cob\Bundle\ApiServicesBundle\Models\ResponseModelTrait
 * @uses \Cob\Bundle\ApiServicesBundle\Models\ServiceClient
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Util\ClassUtil
 */
class BaseResponseModelCollectionTest extends BaseResponseModelTestCase
{
    use ServiceClientMockTrait;

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
            __DIR__ . '/../../Resources/MockResponses/personCollection.json'
        ), true);

        /**
         * @var PersonCollectionWithCountCapability $mockModel
         */
        $mockModel = PersonCollectionWithCountCapability::withData($client, $data['persons']);

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
        $mockModel = PersonCollection::load(
            $this->getServiceClientMockWithJsonData([
                __DIR__ . '/../../Resources/MockResponses/personCollection.json'
            ]),
            []
        );

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
        $mockModel = PersonCollection::loadAsync(
            $this->getServiceClientMockWithJsonData([
                __DIR__ . '/../../Resources/MockResponses/personCollection.json'
            ]),
            []
        );

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

        MockBaseResponseModelWithInit::withData(
            $this->getServiceClientMock([]),
            []
        );
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
        BadMockResponseModelCollection::withData($this->getServiceClientMock(), []);
    }

    /**
     * @covers ::__construct
     * @covers ::count
     * @covers ::loadAsync
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
        $mockModel = PersonCollectionWithCountCapability::loadAsync($client);

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
}
