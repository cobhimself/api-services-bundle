<?php

namespace Cob\Bundle\ApiServicesBundle\Tests\Unit\Models\Loader\Config;

use Cob\Bundle\ApiServicesBundle\Exceptions\LoadConfigRequiredPropertyException;
use Cob\Bundle\ApiServicesBundle\Models\Loader\Config\CollectionLoadConfig;
use Cob\Bundle\ApiServicesBundle\Models\Loader\Config\CollectionLoadConfigBuilder;
use Cob\Bundle\ApiServicesBundle\Tests\ServiceClientMockTrait;
use Cob\Bundle\ApiServicesBundle\Tests\Unit\Mocks\PersonCollection;

/**
 * @codeCoverageIgnore
 * @coversDefaultClass \Cob\Bundle\ApiServicesBundle\Models\Loader\Config\CollectionLoadConfig
 * @covers \Cob\Bundle\ApiServicesBundle\Models\Loader\Config\LoadConfigSharedTrait
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Deserializer
 * @uses \Cob\Bundle\ApiServicesBundle\Models\ServiceClient
 */
class CollectionLoadConfigTest extends CollectionLoadConfigTestCase
{
    use ServiceClientMockTrait;

    /**
     * @covers ::__construct
     * @covers ::getExistingData
     * @covers ::getHandler
     * @covers ::getParent
     * @covers ::getCommandArgs
     * @covers ::getCountCommandArgs
     * @covers ::getClient
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
            $actualCountCommandArgs,
            $actualParent,
            $actualClearCache,
            $actualHandler,
            $actualExistingData
        ) = $actual;

        $loadConfig = new CollectionLoadConfig(
            $actualClient,
            $actualCommandArgs,
            $actualCountCommandArgs,
            $actualParent,
            $actualClearCache,
            $actualHandler,
            $actualExistingData
        );

        $this->confirmLoadConfigAssertions($expected, $loadConfig);
    }

    /**
     * @covers ::__construct
     * @covers ::getExistingData
     * @covers \Cob\Bundle\ApiServicesBundle\Exceptions\LoadConfigRequiredPropertyException
     */
    public function testExceptionThrownOnNullExistingData()
    {
        $this->expectException(LoadConfigRequiredPropertyException::class);
        $this->expectExceptionMessage("Could not obtain the required load configuration property 'existingData'.");

        $client = $this->getServiceClientMock([]);

        $loadConfig = new CollectionLoadConfig(
            $client,
            null,
            null,
            null,
            null,
            null,
            null
        );

        //All other properties can be null but we specifically confirm existing data was set before we get it
        $loadConfig->getExistingData();
    }

    /**
     * @covers ::builder
     * @covers \Cob\Bundle\ApiServicesBundle\Models\Loader\Config\CollectionLoadConfigBuilder
     * @uses \Cob\Bundle\ApiServicesBundle\Models\Util\ClassUtil
     */
    public function testUsing()
    {
        $loadConfigBuilder = CollectionLoadConfig::builder(PersonCollection::class, $this->getServiceClientMock([]));

        $this->assertInstanceOf(CollectionLoadConfigBuilder::class, $loadConfigBuilder);
    }
}