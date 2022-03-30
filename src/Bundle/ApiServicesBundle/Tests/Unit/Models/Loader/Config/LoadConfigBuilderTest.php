<?php

namespace Cob\Bundle\ApiServicesBundle\Tests\Unit\Models\Loader\Config;

use Cob\Bundle\ApiServicesBundle\Models\Loader\Config\LoadConfigBuilder;
use Cob\Bundle\ApiServicesBundle\Tests\ServiceClientMockTrait;
use Cob\Bundle\ApiServicesBundle\Tests\Unit\Mocks\MockBaseRawDataResponseModel;
use Cob\Bundle\ApiServicesBundle\Tests\Unit\Mocks\Person;

/**
 * @codeCoverageIgnore
 * @coversDefaultClass \Cob\Bundle\ApiServicesBundle\Models\Loader\Config\LoadConfigBuilder
 * @covers \Cob\Bundle\ApiServicesBundle\Models\Loader\Config\LoadConfigSharedTrait
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Deserializer
 * @uses \Cob\Bundle\ApiServicesBundle\Models\ServiceClient
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Util\ClassUtil
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Response\BaseResponseModel
 * @uses \Cob\Bundle\ApiServicesBundle\Models\DotData
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\ResponseModelEvent
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\ResponseModelPostExecuteCommandEvent
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\ResponseModelPostLoadEvent
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\ResponseModelPreExecuteCommandEvent
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\ResponseModelPreGetLoadCommandEvent
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\ResponseModelPreLoadEvent
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Loader\AbstractLoader
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Loader\Config\LoadConfig
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Loader\LoadState
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Config\ResponseModelConfig
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Config\ResponseModelConfigSharedTrait
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Response\ResponseModelTrait
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Util\Promise
 */
class LoadConfigBuilderTest extends LoadConfigTestCase
{
    use ServiceClientMockTrait;

    /**
     * @covers ::__construct
     * @covers ::withCommandArgs
     * @covers ::withParent
     * @covers ::clearCache
     * @covers ::handleExceptionsWith
     * @covers ::existingData
     * @covers ::build
     * @covers ::validateModelClass
     * @covers \Cob\Bundle\ApiServicesBundle\Models\Loader\Config\LoadConfig
     * @uses \Cob\Bundle\ApiServicesBundle\Models\ExceptionHandlers\AbstractExceptionHandler
     *
     * @param array $actual
     * @param array $expected
     *
     * @dataProvider dpTestGetters
     */
    public function testGetters(array $actual, array $expected) {
        list(
            $actualClient,
            $actualCommandArgs,
            $actualParent,
            $actualClearCache,
            $actualHandler,
            $actualExistingData
        ) = $actual;

        $builder = new LoadConfigBuilder(Person::class, $actualClient);

        if (!is_null($actualCommandArgs)) {
            $builder->withCommandArgs($actualCommandArgs);
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
     * @uses \Cob\Bundle\ApiServicesBundle\Models\Loader\Loader
     */
    public function testLoad()
    {
        $builder = new LoadConfigBuilder(
            Person::class,
            $this->getServiceClientMockWithJsonData([__DIR__ . '/../../../../Resources/MockResponses/person.json'])
        );

        /**
         * @var Person $person
         */
        $person = $builder->load();

        $this->assertInstanceOf(Person::class, $person);
        $this->assertTrue($person->isLoaded());
        $this->assertNotEmpty($person->dot(''));
    }

    /**
     * @covers ::__construct
     * @covers ::build
     * @covers ::loadAsync
     * @covers ::provide
     * @covers ::validateModelClass
     * @uses \Cob\Bundle\ApiServicesBundle\Models\Loader\AsyncLoader
     */
    public function testLoadAsync()
    {
        $builder = new LoadConfigBuilder(
            Person::class,
            $this->getServiceClientMockWithJsonData([__DIR__ . '/../../../../Resources/MockResponses/person.json'])
        );

        /**
         * @var Person $person
         */
        $person = $builder->loadAsync();

        $this->assertInstanceOf(Person::class, $person);
        $this->assertTrue($person->isWaiting());
        $this->assertNotEmpty($person->dot(''));
        $this->assertTrue($person->isLoaded());
    }

    /**
     * @covers ::__construct
     * @covers ::build
     * @covers ::withData
     * @covers ::provide
     * @covers ::validateModelClass
     * @uses \Cob\Bundle\ApiServicesBundle\Models\Loader\WithDataLoader
     */
    public function testWithData()
    {
        $builder = new LoadConfigBuilder(
            Person::class,
            $this->getServiceClientMock([])
        );

        $data = ['foo' => 'bar'];

        /**
         * @var Person $person
         */
        $person = $builder->withData($data);

        $this->assertInstanceOf(Person::class, $person);
        $this->assertTrue($person->isLoadedWithData());
        $this->assertEquals('bar', $person->dot('foo'));
    }

    /**
     * @covers ::__construct
     * @covers ::build
     * @covers ::withRawData
     * @covers ::provide
     * @covers ::validateModelClass
     * @covers \Cob\Bundle\ApiServicesBundle\Models\Loader\WithRawDataLoader
     * @covers \Cob\Bundle\ApiServicesBundle\Models\Config\ResponseModelConfigBuilder
     * @covers \Cob\Bundle\ApiServicesBundle\Exceptions\ResponseModelSetupException::confirmResponseModelClassSet
     * @uses \Cob\Bundle\ApiServicesBundle\Models\Loader\WithDataLoader
     */
    public function testWithRawData()
    {
        $builder = new LoadConfigBuilder(
            MockBaseRawDataResponseModel::class,
            $this->getServiceClientMock([])
        );

        $data = 'this is raw data';

        /**
         * @var MockBaseRawDataResponseModel $mock
         */
        $mock = $builder->withRawData($data);

        $this->assertInstanceOf(MockBaseRawDataResponseModel::class, $mock);
        $this->assertTrue($mock->isLoadedWithData());
        $this->assertEquals($data, $mock->getRawData());
    }
}
