<?php

namespace Cob\Bundle\ApiServicesBundle\Tests\Unit\Models\Loader\Config;

use Cob\Bundle\ApiServicesBundle\Exceptions\LoadConfigRequiredPropertyException;
use Cob\Bundle\ApiServicesBundle\Models\Loader\Config\LoadConfig;
use Cob\Bundle\ApiServicesBundle\Models\Loader\Config\LoadConfigBuilder;
use Cob\Bundle\ApiServicesBundle\Tests\ServiceClientMockTrait;
use Cob\Bundle\ApiServicesBundle\Tests\Unit\Mocks\Person;

/**
 * @codeCoverageIgnore
 * @coversDefaultClass \Cob\Bundle\ApiServicesBundle\Models\Loader\Config\LoadConfig
 * @covers \Cob\Bundle\ApiServicesBundle\Models\Loader\Config\LoadConfigSharedTrait
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Deserializer
 * @uses \Cob\Bundle\ApiServicesBundle\Models\ServiceClient
 */
class LoadConfigTest extends LoadConfigTestCase
{
    use ServiceClientMockTrait;

    /**
     * @covers ::__construct
     * @covers ::getExistingData
     * @covers ::getExceptionHandler
     * @covers ::getParent
     * @covers ::getCommandArgs
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
            $actualParent,
            $actualClearCache,
            $actualHandler,
            $actualExistingData
        ) = $actual;

        $loadConfig = new LoadConfig(
            $actualClient,
            $actualCommandArgs,
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

        $loadConfig = new LoadConfig(
            $client,
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
     * @covers \Cob\Bundle\ApiServicesBundle\Models\Loader\Config\LoadConfigBuilder
     * @uses \Cob\Bundle\ApiServicesBundle\Models\Util\ClassUtil
     */
    public function testUsing()
    {
        $loadConfigBuilder = LoadConfig::builder(Person::class, $this->getServiceClientMock([]));

        $this->assertInstanceOf(LoadConfigBuilder::class, $loadConfigBuilder);
    }
}
