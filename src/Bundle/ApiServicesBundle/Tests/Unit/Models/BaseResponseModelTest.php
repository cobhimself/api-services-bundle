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
 */
class BaseResponseModelTest extends BaseResponseModelTestCase
{
    use ServiceClientMockTrait;

    /**
     * @covers ::__construct
     * @covers ::withData
     * @covers ::isLoadedWithData
     * @covers ::getResponseModelConfig
     * @covers \Cob\Bundle\ApiServicesBundle\Models\Loader\WithDataLoader
     * @covers \Cob\Bundle\ApiServicesBundle\Models\Loader\AbstractLoader
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
     * @covers ::getResponseModelConfig
     * @covers ::isLoaded
     * @covers \Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\ResponseModelEvent
     * @covers \Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\ResponseModelPostExecuteCommandEvent
     * @covers \Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\ResponseModelPostLoadEvent
     * @covers \Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\ResponseModelPreExecuteCommandEvent
     * @covers \Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\ResponseModelPreGetLoadCommandEvent
     * @covers \Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\ResponseModelPreLoadEvent
     * @covers \Cob\Bundle\ApiServicesBundle\Models\Loader\Loader
     * @covers \Cob\Bundle\ApiServicesBundle\Models\Loader\AbstractLoader
     * @covers \Cob\Bundle\ApiServicesBundle\Models\Util\Promise
     */
    public function testLoad()
    {
        /**
         * @var MockBaseResponseModel $mockModel
         */
        $mockModel = MockBaseResponseModel::load(
            $this->getServiceClientMockWithResponseData(self::MOCK_RESPONSE_DATA),
            ['id' => 1]
        );

        $this->assertTrue($mockModel->isLoaded());
        $this->assertEquals(1, $mockModel->dot('data.one'));
        $this->assertEquals(2, $mockModel->dot('data.two'));
        $this->assertSame(self::MOCK_INNER_RESPONSE_DATA, $mockModel->dot('data'));
    }

    /**
     * @covers ::__construct
     * @covers ::loadAsync
     * @covers ::getResponseModelConfig
     * @covers ::isLoaded
     * @covers ::isWaiting
     * @covers \Cob\Bundle\ApiServicesBundle\Models\Loader\AbstractLoader
     * @covers \Cob\Bundle\ApiServicesBundle\Models\Loader\AsyncLoader
     * @covers \Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\ResponseModelEvent
     * @covers \Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\ResponseModelPostExecuteCommandEvent
     * @covers \Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\ResponseModelPostLoadEvent
     * @covers \Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\ResponseModelPreExecuteCommandEvent
     * @covers \Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\ResponseModelPreGetLoadCommandEvent
     * @covers \Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\ResponseModelPreLoadEvent
     * @covers \Cob\Bundle\ApiServicesBundle\Models\Util\Promise
     */
    public function testLoadAsync()
    {
        /**
         * @var MockBaseResponseModel $mockModel
         */
        $mockModel = MockBaseResponseModel::loadAsync(
            $this->getServiceClientMockWithResponseData(self::MOCK_RESPONSE_DATA),
            ['id' => 1]
        );

        $this->assertTrue($mockModel->isWaiting());
        $this->assertEquals(1, $mockModel->dot('data.one'));
        $this->assertTrue($mockModel->isLoaded());
        $this->assertEquals(2, $mockModel->dot('data.two'));
        $this->assertSame(self::MOCK_INNER_RESPONSE_DATA, $mockModel->dot('data'));
    }

    /**
     * @covers ::__construct
     * @covers ::getResponseModelConfig
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
     * @covers ::getResponseModelConfig
     * @covers ::withData
     */
    public function testSetupException()
    {
        $this->expectException(ResponseModelSetupException::class);
        BadMockResponseModel::withData($this->getServiceClientMock(), []);
    }
}
