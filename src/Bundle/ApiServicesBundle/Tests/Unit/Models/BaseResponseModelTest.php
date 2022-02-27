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
use Cob\Bundle\ApiServicesBundle\Tests\ServiceClientMockTrait;
use Cob\Bundle\ApiServicesBundle\Tests\Unit\BaseResponseModelTestCase;
use Cob\Bundle\ApiServicesBundle\Tests\Unit\Mocks\BadMockResponseModel;
use Cob\Bundle\ApiServicesBundle\Tests\Unit\Mocks\MockBaseResponseModel;
use Cob\Bundle\ApiServicesBundle\Tests\Unit\Mocks\MockBaseResponseModelWithInit;
use Cob\Bundle\ApiServicesBundle\Tests\Unit\Mocks\Person;

/**
 * @codeCoverageIgnore
 * @coversDefaultClass \Cob\Bundle\ApiServicesBundle\Models\BaseResponseModel
 *
 * @uses \Cob\Bundle\ApiServicesBundle\Models\ServiceClient
 * @uses \Cob\Bundle\ApiServicesBundle\Exceptions\BaseApiServicesBundleException
 * @uses \Cob\Bundle\ApiServicesBundle\Models\ResponseModelTrait
 * @uses \Cob\Bundle\ApiServicesBundle\Models\ResponseModelConfig
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Util\ClassUtil
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Deserializer
 * @uses \Cob\Bundle\ApiServicesBundle\Models\DotData
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Loader\Loader
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Loader\State\LoadState
 * @uses \Cob\Bundle\ApiServicesBundle\Models\BaseResponseModelCollection
 * @uses \Cob\Bundle\ApiServicesBundle\Models\ResponseModelConfigSharedTrait
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Loader\AbstractLoader
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Loader\WithDataCollectionLoader
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Loader\WithDataLoader
 * @uses \Cob\Bundle\ApiServicesBundle\Models\ResponseModelCollectionConfig
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\ResponseModelEvent
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\ResponseModelPostExecuteCommandEvent
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\ResponseModelPostLoadEvent
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\ResponseModelPreExecuteCommandEvent
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\ResponseModelPreLoadEvent
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\ResponseModelPreGetLoadCommandEvent
 */
class BaseResponseModelTest extends BaseResponseModelTestCase
{
    use ServiceClientMockTrait;

    /**
     * @covers ::__construct
     * @covers ::withData
     * @covers ::isLoadedWithData
     * @covers ::getConfig
     * @covers \Cob\Bundle\ApiServicesBundle\Models\Loader\WithDataLoader
     * @covers \Cob\Bundle\ApiServicesBundle\Models\Loader\AbstractCollectionLoader
     */
    public function testWithData()
    {
        $client = $this->getServiceClientMock();

        /**
         * @var MockBaseResponseModel $mockModel
         */
        $mockModel = MockBaseResponseModel::withData($client, self::MOCK_RESPONSE_DATA);

        $this->assertTrue($mockModel->isLoadedWithData());
        $this->assertSame(self::MOCK_RESPONSE_DATA, $mockModel->toArray());
        $this->assertEquals(1, $mockModel->dot('data.one'));
    }

    /**
     * @covers ::__construct
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
        /**
         * @var Person $mockModel
         */
        $mockModel = Person::load(
            $this->getServiceClientMockWithJsonData([
                __DIR__ . '/../../Resources/MockResponses/person.json'
            ]),
            []
        );

        $this->assertTrue($mockModel->isLoaded());
        $this->assertEquals("Person 1", $mockModel->getName());
        $this->assertEquals(1, $mockModel->getAge());
        $this->assertEmpty($mockModel->getChildren());
    }

    /**
     * @covers ::__construct
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
        /**
         * @var Person $mockModel
         */
        $mockModel = Person::loadAsync(
            $this->getServiceClientMockWithJsonData([
                __DIR__ . '/../../Resources/MockResponses/person.json'
            ]),
            []
        );

        $this->assertTrue($mockModel->isWaiting());
        $this->assertEquals("Person 1", $mockModel->getName());
        //Now that we've attempted to get a value, we should be loaded
        $this->assertTrue($mockModel->isLoaded());
        $this->assertEquals(1, $mockModel->getAge());
        $this->assertEmpty($mockModel->getChildren());
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
     */
    public function testSetupException()
    {
        $this->expectException(ResponseModelSetupException::class);
        BadMockResponseModel::withData($this->getServiceClientMock(), []);
    }

    /**
     * @covers ::__construct
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
        /**
         * @var Person $mockModel
         */
        $mockModel = Person::load(
            $this->getServiceClientMockWithJsonData([
                __DIR__ . '/../../Resources/MockResponses/personWithChildren.json'
            ]),
            []
        );

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
}
