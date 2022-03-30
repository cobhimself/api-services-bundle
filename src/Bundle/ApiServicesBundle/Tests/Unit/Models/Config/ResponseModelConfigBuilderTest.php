<?php

namespace Cob\Bundle\ApiServicesBundle\Tests\Unit\Models\Config;

use Cob\Bundle\ApiServicesBundle\Exceptions\ResponseModelSetupException;
use Cob\Bundle\ApiServicesBundle\Models\Config\ResponseModelCollectionConfigBuilder;
use Cob\Bundle\ApiServicesBundle\Models\Config\ResponseModelConfig;
use Cob\Bundle\ApiServicesBundle\Models\Config\ResponseModelConfigBuilder;
use Cob\Bundle\ApiServicesBundle\Models\ExceptionHandlers\ExceptionHandlerInterface;
use Cob\Bundle\ApiServicesBundle\Models\ExceptionHandlers\ResponseModelExceptionHandler;
use Cob\Bundle\ApiServicesBundle\Tests\ResponseModelCollectionConfigTestCase;
use Cob\Bundle\ApiServicesBundle\Tests\Unit\Mocks\MockBaseResponseModel;
use Cob\Bundle\ApiServicesBundle\Tests\Unit\Mocks\Person;
use Cob\Bundle\ApiServicesBundle\Tests\Unit\Mocks\PersonCollection;
use Cob\Bundle\ApiServicesBundle\Tests\Unit\Mocks\PersonCollectionWithCountCapability;
use Cob\Bundle\ApiServicesBundle\Tests\Unit\Models\Response\BaseResponseModelTestCase;
use InvalidArgumentException;

/**
 * @codeCoverageIgnore
 * @coversDefaultClass \Cob\Bundle\ApiServicesBundle\Models\Config\ResponseModelConfigBuilder
 * @uses \Cob\Bundle\ApiServicesBundle\Exceptions\BaseApiServicesBundleException
 * @uses \Cob\Bundle\ApiServicesBundle\Models\ExceptionHandlers\AbstractExceptionHandler
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Util\ClassUtil
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Config\ResponseModelConfigSharedTrait
 */
class ResponseModelConfigBuilderTest extends BaseResponseModelTestCase
{

    private function getBaseBuilder(): ResponseModelConfigBuilder {
        return (new ResponseModelConfigBuilder())
            ->responseModelClass(Person::class);
    }

    /**
     * @covers ::build
     * @covers ::validate
     * @covers \Cob\Bundle\ApiServicesBundle\Exceptions\ResponseModelSetupException::confirmResponseModelClassSet
     */
    public function testNoResponseModelClassSet()
    {
        $this->expectException(ResponseModelSetupException::class);
        $this->expectExceptionMessage('A response model class must be provided!');

        (new ResponseModelConfigBuilder())->build();
    }

    /**
     * @covers ::build
     * @covers ::validate
     * @covers ::responseModelClass
     * @covers \Cob\Bundle\ApiServicesBundle\Exceptions\ResponseModelSetupException
     * @uses \Cob\Bundle\ApiServicesBundle\Models\Config\ResponseModelConfig
     */
    public function testDefaults()
    {
        $config = $this->getBaseBuilder()->build();

        $this->assertEquals(Person::class, $config->getResponseModelClass());
        $this->assertEquals('', $config->getCommand());
        $this->assertEquals([], $config->getDefaultArgs());
        $this->assertFalse($config->holdsRawData());
        $this->assertEquals([], $config->getInitCallbacks());
        $this->assertInstanceOf(ExceptionHandlerInterface::class, $config->getDefaultExceptionHandler());
    }

    /**
     * @covers ::initCallbacks
     * @covers ::responseModelClass
     */
    public function testInitCallbacksWithNonCallable()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The provided callback array MUST contain");

        $this->getBaseBuilder()
            ->initCallbacks(['not', 'callable']);
    }

    /**
     * @covers ::extend
     * @covers ::addInitCallback
     * @covers ::initCallbacks
     * @covers ::build
     * @covers ::command
     * @covers ::defaultArgs
     * @covers ::defaultExceptionHandler
     * @covers ::holdsRawData
     * @covers ::responseModelClass
     * @covers ::validate
     * @covers \Cob\Bundle\ApiServicesBundle\Exceptions\ResponseModelSetupException
     * @uses \Cob\Bundle\ApiServicesBundle\Models\Config\ResponseModelConfig
     */
    public function testSettersAndExtend()
    {
        $callbacks = [
            function () {
                //blank
            }
        ];
        $defaultExceptionHandler = ResponseModelExceptionHandler::ignore();

        $config = $this->getBaseBuilder()
            ->command(self::TEST_COMMAND_NAME)
            ->defaultArgs(self::TEST_COMMAND_ARGS)
            ->holdsRawData()
            ->initCallbacks($callbacks)
            ->defaultExceptionHandler($defaultExceptionHandler)
            ->build();

        $this->assertEquals(Person::class, $config->getResponseModelClass());
        $this->assertEquals(self::TEST_COMMAND_NAME, $config->getCommand());
        $this->assertEquals(self::TEST_COMMAND_ARGS, $config->getDefaultArgs());
        $this->assertTrue($config->holdsRawData());
        $this->assertEquals($callbacks, $config->getInitCallbacks());
        $this->assertEquals($defaultExceptionHandler, $config->getDefaultExceptionHandler());

        //Now let's extend our config and change a few things to confirm
        $config = ResponseModelConfigBuilder::extend($config)
            ->responseModelClass(MockBaseResponseModel::class)
            ->build();

        $this->assertEquals(MockBaseResponseModel::class, $config->getResponseModelClass());
        $this->assertEquals(self::TEST_COMMAND_NAME, $config->getCommand());
        $this->assertEquals(self::TEST_COMMAND_ARGS, $config->getDefaultArgs());
        $this->assertTrue($config->holdsRawData());
        $this->assertEquals($callbacks, $config->getInitCallbacks());
        $this->assertEquals($defaultExceptionHandler, $config->getDefaultExceptionHandler());
    }
}
