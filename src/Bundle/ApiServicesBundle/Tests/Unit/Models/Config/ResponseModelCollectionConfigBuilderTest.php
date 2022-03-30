<?php

namespace Cob\Bundle\ApiServicesBundle\Tests\Unit\Models\Config;

use Cob\Bundle\ApiServicesBundle\Exceptions\ResponseModelSetupException;
use Cob\Bundle\ApiServicesBundle\Models\Config\ResponseModelCollectionConfigBuilder;
use Cob\Bundle\ApiServicesBundle\Models\ExceptionHandlers\ResponseModelExceptionHandler;
use Cob\Bundle\ApiServicesBundle\Tests\ResponseModelCollectionConfigTestCase;
use Cob\Bundle\ApiServicesBundle\Tests\Unit\Mocks\MockBaseResponseModel;
use Cob\Bundle\ApiServicesBundle\Tests\Unit\Mocks\Person;
use Cob\Bundle\ApiServicesBundle\Tests\Unit\Mocks\PersonCollection;
use Cob\Bundle\ApiServicesBundle\Tests\Unit\Mocks\PersonCollectionWithCountCapability;
use InvalidArgumentException;

/**
 * @codeCoverageIgnore
 * @coversDefaultClass \Cob\Bundle\ApiServicesBundle\Models\Config\ResponseModelCollectionConfigBuilder
 * @uses \Cob\Bundle\ApiServicesBundle\Exceptions\BaseApiServicesBundleException
 * @uses \Cob\Bundle\ApiServicesBundle\Models\ExceptionHandlers\AbstractExceptionHandler
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Util\ClassUtil
 */
class ResponseModelCollectionConfigBuilderTest extends ResponseModelCollectionConfigTestCase
{

    private function getBaseBuilder(): ResponseModelCollectionConfigBuilder {
        return (new ResponseModelCollectionConfigBuilder())
            ->responseModelClass(PersonCollection::class)
            ->childResponseModelClass(Person::class);
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

        (new ResponseModelCollectionConfigBuilder())->build();
    }

    /**
     * @covers ::build
     * @covers ::validate
     * @covers ::responseModelClass
     * @covers \Cob\Bundle\ApiServicesBundle\Exceptions\ResponseModelSetupException
     * @uses \Cob\Bundle\ApiServicesBundle\Models\Config\ResponseModelConfigSharedTrait
     */
    public function testNoChildResponseModelClassSet()
    {
        $this->expectException(ResponseModelSetupException::class);
        $this->expectExceptionMessage('property to be set!');

        (new ResponseModelCollectionConfigBuilder())
            ->responseModelClass(PersonCollection::class)
            ->build();
    }

    /**
     * @covers ::build
     * @covers ::validate
     * @covers ::responseModelClass
     * @covers ::childResponseModelClass
     * @covers \Cob\Bundle\ApiServicesBundle\Exceptions\ResponseModelSetupException
     * @uses \Cob\Bundle\ApiServicesBundle\Models\Config\ResponseModelCollectionConfig
     * @uses \Cob\Bundle\ApiServicesBundle\Models\Config\ResponseModelConfigSharedTrait
     */
    public function testDefaults()
    {
        $config = $this->getBaseBuilder()->build();

        $this->confirmDefaults($config);
    }

    /**
     * @covers ::build
     * @covers ::validate
     * @covers ::responseModelClass
     * @covers ::childResponseModelClass
     * @covers ::command
     * @covers ::defaultArgs
     * @covers ::collectionPath
     * @covers ::countCommand
     * @covers ::countArgs
     * @covers ::countValuePath
     * @covers ::loadMaxResults
     * @covers ::buildCountArgsCallback
     * @covers ::chunkCommandMaxResults
     * @covers ::initCallbacks
     * @covers ::defaultExceptionHandler
     * @covers ::addInitCallback
     * @covers ::extend
     * @covers \Cob\Bundle\ApiServicesBundle\Exceptions\ResponseModelSetupException
     * @uses \Cob\Bundle\ApiServicesBundle\Models\Config\ResponseModelCollectionConfig
     * @uses \Cob\Bundle\ApiServicesBundle\Models\Config\ResponseModelConfigSharedTrait
     */
    public function testAllSettersAndExtend()
    {
        $collectionPath = 'collection.path';
        $countCommand = 'CountCommand';
        $countArgs = ['count' => 'args'];
        $countValuePath = 'count.value.path';
        $loadMaxResults = 2;
        $buildCountArgsCallback = function () {
            //blank
        };
        $chunkCommandMaxResults = 2;
        $initCallbacks = [function () {
            //blank
        }];
        $defaultExceptionHandler = ResponseModelExceptionHandler::ignore();

        $config = $this->getBaseBuilder()
            ->command(self::TEST_COMMAND_NAME)
            ->defaultArgs(self::TEST_COMMAND_ARGS)
            ->collectionPath($collectionPath)
            ->countCommand($countCommand)
            ->countArgs($countArgs)
            ->countValuePath($countValuePath)
            ->loadMaxResults($loadMaxResults)
            ->buildCountArgsCallback($buildCountArgsCallback)
            ->chunkCommandMaxResults($chunkCommandMaxResults)
            ->initCallbacks($initCallbacks)
            ->defaultExceptionHandler($defaultExceptionHandler)
            ->build();

        $this->assertEquals(PersonCollection::class, $config->getResponseModelClass());
        $this->assertEquals(Person::class, $config->getChildResponseModelClass());
        $this->assertEquals(self::TEST_COMMAND_NAME, $config->getCommand());
        $this->assertEquals(self::TEST_COMMAND_ARGS, $config->getDefaultArgs());
        $this->assertEquals($collectionPath, $config->getCollectionPath());
        $this->assertEquals($countCommand, $config->getCountCommand());
        $this->assertEquals($countArgs, $config->getCountArgs());
        $this->assertEquals($countValuePath, $config->getCountValuePath());
        $this->assertEquals($loadMaxResults, $config->getLoadMaxResults());
        $this->assertEquals($buildCountArgsCallback, $config->getBuildCountArgsCallback());
        $this->assertEquals($chunkCommandMaxResults, $config->getChunkCommandMaxResults());
        $this->assertEquals($initCallbacks, $config->getInitCallbacks());
        $this->assertEquals($defaultExceptionHandler, $config->getDefaultExceptionHandler());

        //Now let's extend the config, change some stuff, build, and confirm!
        $config = ResponseModelCollectionConfigBuilder::extend($config)
            ->responseModelClass(PersonCollectionWithCountCapability::class)
            ->childResponseModelClass(MockBaseResponseModel::class)
            ->build();

        $this->assertEquals(PersonCollectionWithCountCapability::class, $config->getResponseModelClass());
        $this->assertEquals(MockBaseResponseModel::class, $config->getChildResponseModelClass());

        $this->assertEquals(self::TEST_COMMAND_NAME, $config->getCommand());
        $this->assertEquals(self::TEST_COMMAND_ARGS, $config->getDefaultArgs());
        $this->assertEquals($collectionPath, $config->getCollectionPath());
        $this->assertEquals($countCommand, $config->getCountCommand());
        $this->assertEquals($countArgs, $config->getCountArgs());
        $this->assertEquals($countValuePath, $config->getCountValuePath());
        $this->assertEquals($loadMaxResults, $config->getLoadMaxResults());
        $this->assertEquals($buildCountArgsCallback, $config->getBuildCountArgsCallback());
        $this->assertEquals($chunkCommandMaxResults, $config->getChunkCommandMaxResults());
        $this->assertEquals($initCallbacks, $config->getInitCallbacks());
        $this->assertEquals($defaultExceptionHandler, $config->getDefaultExceptionHandler());
    }

    /**
     * @covers ::initCallbacks
     * @covers ::childResponseModelClass
     * @covers ::responseModelClass
     */
    public function testInitCallbacksWithNonCallable()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The provided callback array MUST contain");

        $this->getBaseBuilder()
            ->initCallbacks(['not', 'callable']);
    }
}
