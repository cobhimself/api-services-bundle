<?php

namespace Cob\Bundle\ApiServicesBundle\Tests\Unit\Models\Config;

use Cob\Bundle\ApiServicesBundle\Exceptions\ResponseModelSetupException;
use Cob\Bundle\ApiServicesBundle\Models\Config\ResponseModelCollectionConfig;
use Cob\Bundle\ApiServicesBundle\Models\ExceptionHandlers\ExceptionHandlerInterface;
use Cob\Bundle\ApiServicesBundle\Models\ExceptionHandlers\ResponseModelExceptionHandler;
use Cob\Bundle\ApiServicesBundle\Tests\ResponseModelCollectionConfigTestCase;
use Cob\Bundle\ApiServicesBundle\Tests\Unit\Mocks\Person;
use Cob\Bundle\ApiServicesBundle\Tests\Unit\Mocks\PersonCollection;
use Cob\Bundle\ApiServicesBundle\Tests\Unit\Models\Response\BaseResponseModelTestCase;

/**
 * @codeCoverageIgnore
 * @coversDefaultClass \Cob\Bundle\ApiServicesBundle\Models\Config\ResponseModelCollectionConfig
 * @covers \Cob\Bundle\ApiServicesBundle\Models\Util\ClassUtil
 * @covers \Cob\Bundle\ApiServicesBundle\Models\Config\ResponseModelConfigSharedTrait
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Loader\AbstractCollectionLoader
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Deserializer
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Loader\Config\CollectionLoadConfig
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Loader\Config\CollectionLoadConfigBuilder
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Loader\Config\LoadConfigSharedTrait
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Loader\LoadState
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Loader\WithDataCollectionLoader
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Response\ResponseModelTrait
 * @uses \Cob\Bundle\ApiServicesBundle\Models\ServiceClient
 * @uses \Cob\Bundle\ApiServicesBundle\Models\DotData
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Util\LogUtil
 * @uses \Cob\Bundle\ApiServicesBundle\Models\HasOutputTrait
 */
class ResponseModelCollectionConfigTest extends ResponseModelCollectionConfigTestCase {

    /**
     * @covers ::__construct
     * @covers ::getChildResponseModelClass
     * @covers ::getChunkCommandMaxResults
     * @covers ::getCollectionPath
     * @covers ::getCountArgs
     * @covers ::getCountCommand
     * @covers ::getCountValuePath
     * @covers ::getLoadMaxResults
     * @covers ::hasBuildCountArgsCallback
     * @uses \Cob\Bundle\ApiServicesBundle\Models\ExceptionHandlers\AbstractExceptionHandler
     */
    public function testConstructorDefaults()
    {
        $config = new ResponseModelCollectionConfig(
            PersonCollection::class,
            Person::class
        );

        $this->confirmDefaults($config);
    }

    /**
     * @covers ::__construct
     * @covers ::getResponseModelClass
     * @covers ::getChildResponseModelClass
     * @covers ::getCommand
     * @covers ::getDefaultArgs
     * @covers ::getCollectionPath
     * @covers ::getCountCommand
     * @covers ::getCountArgs
     * @covers ::getCountValuePath
     * @covers ::getLoadMaxResults
     * @covers ::getBuildCountArgsCallback
     * @covers ::getChunkCommandMaxResults
     * @covers ::getInitCallbacks
     * @covers ::getDefaultExceptionHandler
     * @covers ::hasBuildCountArgsCallback
     * @uses \Cob\Bundle\ApiServicesBundle\Models\ExceptionHandlers\AbstractExceptionHandler
     */
    public function testConstructAndGetters()
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

        $config = new ResponseModelCollectionConfig(
            PersonCollection::class,
            Person::class,
            self::TEST_COMMAND_NAME,
            self::TEST_COMMAND_ARGS,
            $collectionPath,
            $countCommand,
            $countArgs,
            $countValuePath,
            $loadMaxResults,
            $buildCountArgsCallback,
            $chunkCommandMaxResults,
            $initCallbacks,
            $defaultExceptionHandler
        );

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
    }

    /**
     * @covers ::__construct
     * @covers ::getBuildCountArgsCallback
     * @covers ::hasBuildCountArgsCallback
     * @covers \Cob\Bundle\ApiServicesBundle\Exceptions\ResponseModelSetupException
     * @covers \Cob\Bundle\ApiServicesBundle\Exceptions\BaseApiServicesBundleException
     */
    public function testNoBuildCountArgsCallback()
    {
        $this->expectException(ResponseModelSetupException::class);
        $this->expectExceptionMessage('Cannot obtain the buildCountArgsCallback');

        $config = new ResponseModelCollectionConfig(
            PersonCollection::class,
            Person::class
        );

        $config->getBuildCountArgsCallback();
    }

    /**
     * @covers ::__construct
     * @covers ::doInits
     * @covers \Cob\Bundle\ApiServicesBundle\Models\Response\Collection\BaseResponseModelCollection
     * @covers \Cob\Bundle\ApiServicesBundle\Models\Config\ResponseModelCollectionConfig
     * @covers \Cob\Bundle\ApiServicesBundle\Models\Config\ResponseModelCollectionConfigBuilder
     * @covers \Cob\Bundle\ApiServicesBundle\Exceptions\ResponseModelSetupException
     */
    public function testInits()
    {
        $client = $this->getServiceClientMock([]);
        $callbackCalled = false;
        /**
         * @var PersonCollection $model
         */
        $model = PersonCollection::using($client)->withData([]);

        $config = new ResponseModelCollectionConfig(
            PersonCollection::class,
            Person::class,
            self::TEST_COMMAND_NAME,
            self::TEST_COMMAND_ARGS,
            '',
            null,
            [],
            '',
            100,
            null,
            10,
            [
                function (PersonCollection $innerModel) use ($model, &$callbackCalled) {
                    $callbackCalled = true;
                    $this->assertSame($model, $innerModel);
                }
            ]
        );

        $config->doInits($model);

        $this->assertTrue($callbackCalled);
    }

    /**
     * @covers ::__construct
     * @covers ::__toString
     */
    public function testToString()
    {
        $config = new ResponseModelCollectionConfig(
            PersonCollection::class,
            Person::class,
            self::TEST_COMMAND_NAME,
            self::TEST_COMMAND_ARGS,
            '',
            null,
            [],
            '',
            100,
            null,
            10,
            [function () { /* intentionally blank */ }]
        );

        $expected = [];
        $expected[] = 'Response Model Collection Config:';
        $expected[] = ' > Model: Cob\Bundle\ApiServicesBundle\Tests\Unit\Mocks\PersonCollection';
        $expected[] = ' > Child Models: Cob\Bundle\ApiServicesBundle\Tests\Unit\Mocks\Person';
        $expected[] = ' > Command: TestCommand';
        $expected[] = ' > Default Args: ["arg1","arg2"]';
        $expected[] = ' > Collection Path: ';
        $expected[] = ' > Count Command: ';
        $expected[] = ' > Count Args: []';
        $expected[] = ' > Count Value Path: ';
        $expected[] = ' > Load Max Results: 100';
        $expected[] = ' > Build Count Args Callback: false';
        $expected[] = ' > Chunk Command Max Results: 10';
        $expected[] = ' > Init Callbacks: true';
        $expected[] = ' > Default Exception Handler: false';

        $this->assertEquals(join(PHP_EOL, $expected) . PHP_EOL, (string) $config);
    }
}
