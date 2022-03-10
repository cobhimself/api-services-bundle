<?php

namespace Cob\Bundle\ApiServicesBundle\Tests\Unit\Models\Loader\Config;

use Cob\Bundle\ApiServicesBundle\Exceptions\LoadConfigRequiredPropertyException;
use Cob\Bundle\ApiServicesBundle\Exceptions\ResponseModelException;
use Cob\Bundle\ApiServicesBundle\Models\ExceptionHandlers\ResponseModelExceptionHandler;
use Cob\Bundle\ApiServicesBundle\Models\Loader\Config\LoadConfig;
use Cob\Bundle\ApiServicesBundle\Models\Loader\Config\LoadConfigBuilder;
use Cob\Bundle\ApiServicesBundle\Tests\ServiceClientMockTrait;
use Cob\Bundle\ApiServicesBundle\Tests\Unit\Mocks\Person;
use Generator;
use PHPUnit\Framework\TestCase;

/**
 * @codeCoverageIgnore
 * @coversDefaultClass \Cob\Bundle\ApiServicesBundle\Models\Loader\Config\LoadConfig
 * @covers \Cob\Bundle\ApiServicesBundle\Models\Loader\Config\LoadConfigSharedTrait
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Deserializer
 * @uses \Cob\Bundle\ApiServicesBundle\Models\ServiceClient
 */
class LoadConfigTest extends TestCase
{
    use ServiceClientMockTrait;

    /**
     * @covers ::__construct
     * @covers ::getExistingData
     * @covers ::getHandler
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

        list(
            $expectedClient,
            $expectedCommandArgs,
            $expectedParent,
            $expectedClearCache,
            $expectedHandler,
            $expectedExistingData
        ) = $expected;

        $loadConfig = new LoadConfig(
            $actualClient,
            $actualCommandArgs,
            $actualParent,
            $actualClearCache,
            $actualHandler,
            $actualExistingData
        );

        $this->assertEquals($expectedClient, $loadConfig->getClient());
        $this->assertEquals($expectedCommandArgs, $loadConfig->getCommandArgs());
        $this->assertEquals($expectedParent, $loadConfig->getParent());
        $this->assertEquals($expectedClearCache, $loadConfig->doClearCache());
        $this->assertEquals($expectedHandler, $loadConfig->getHandler());
        $this->assertEquals($expectedExistingData, $loadConfig->getExistingData());
    }

    public function dpTestGetters(): Generator
    {
        $client = $this->getServiceClientMock([]);
        $handler = new ResponseModelExceptionHandler();
        $parent = Person::using($client)->withData([]);

        //Defaults
        yield [
            'actual' => [
                $client,
                null,
                null,
                null,
                null,
                //Can't pass in null for existing data because we'll get an exception saying this data hasn't been
                //defined. We'll test the exception separately.
                []
            ],
            'expected' => [
                $client,
                [],
                null,
                false,
                ResponseModelExceptionHandler::passThruAndWrapWith(
                    ResponseModelException::class,
                    ['An exception was thrown during loading']
                ),
                []
            ]
        ];

        $commandArgs = ['foo' => 'bar'];
        $existingData = ['existing' => 'data'];

        //All values given
        yield [
            'actual' => [
                $client,
                $commandArgs,
                $parent,
                true,
                $handler,
                $existingData
            ],
            'expected' => [
                $client,
                $commandArgs,
                $parent,
                true,
                $handler,
                $existingData
            ]
        ];
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