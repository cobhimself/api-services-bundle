<?php

namespace Cob\Bundle\ApiServicesBundle\Tests\Unit\Models\Config;

use Cob\Bundle\ApiServicesBundle\Exceptions\ResponseModelSetupException;
use Cob\Bundle\ApiServicesBundle\Models\Config\ResponseModelCollectionConfig;
use Cob\Bundle\ApiServicesBundle\Tests\Unit\BaseResponseModelTestCase;
use Cob\Bundle\ApiServicesBundle\Tests\Unit\Mocks\Person;
use Cob\Bundle\ApiServicesBundle\Tests\Unit\Mocks\PersonCollection;

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
 * @uses \Cob\Bundle\ApiServicesBundle\Models\ResponseModelTrait
 * @uses \Cob\Bundle\ApiServicesBundle\Models\ServiceClient
 * @uses \Cob\Bundle\ApiServicesBundle\Models\DotData
 */
class ResponseModelCollectionConfigTest extends BaseResponseModelTestCase {

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
     */
    public function testConstructAndGetters()
    {
        $collectionPath = 'collection.path';
        $countCommand = 'CountCommand';
        $countArgs = ['count' => 'args'];
        $countValuePath = 'count.value.path';
        $loadMaxResults = 2;
        $buildCountArgsCallback = function () {};

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
            $buildCountArgsCallback
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
    }

    /**
     * @covers ::__construct
     * @covers ::getBuildCountArgsCallback
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
     * @covers \Cob\Bundle\ApiServicesBundle\Models\BaseResponseModelCollection
     * @covers \Cob\Bundle\ApiServicesBundle\Models\Config\ResponseModelCollectionConfig
     * @covers \Cob\Bundle\ApiServicesBundle\Models\Config\ResponseModelCollectionConfigBuilder
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

}
