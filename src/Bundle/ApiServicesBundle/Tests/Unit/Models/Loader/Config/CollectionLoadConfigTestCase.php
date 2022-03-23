<?php

namespace Cob\Bundle\ApiServicesBundle\Tests\Unit\Models\Loader\Config;

use Cob\Bundle\ApiServicesBundle\Exceptions\ResponseModelException;
use Cob\Bundle\ApiServicesBundle\Models\ExceptionHandlers\ResponseModelExceptionHandler;
use Cob\Bundle\ApiServicesBundle\Models\Loader\Config\CollectionLoadConfig;
use Cob\Bundle\ApiServicesBundle\Tests\ServiceClientMockTrait;
use Cob\Bundle\ApiServicesBundle\Tests\Unit\Mocks\Person;
use Generator;
use PHPUnit\Framework\TestCase;

/**
 * @codeCoverageIgnore
 */
abstract class CollectionLoadConfigTestCase extends TestCase
{
    use ServiceClientMockTrait;

    abstract public function testGetters(array $actual, array $expected);

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
                null,
                //Can't pass in null for existing data because we'll get an exception saying this data hasn't been
                //defined. We'll test the exception separately.
                []
            ],
            'expected' => [
                $client,
                [],
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
        $countCommandArgs = ['max' => 'whatever'];
        $existingData = ['existing' => 'data'];

        //All values given
        yield [
            'actual' => [
                $client,
                $commandArgs,
                $countCommandArgs,
                $parent,
                true,
                $handler,
                $existingData
            ],
            'expected' => [
                $client,
                $commandArgs,
                $countCommandArgs,
                $parent,
                true,
                $handler,
                $existingData
            ]
        ];
    }

    protected function confirmLoadConfigAssertions(array $expected, CollectionLoadConfig $loadConfig)
    {
        list(
            $expectedClient,
            $expectedCommandArgs,
            $expectedCountCommandArgs,
            $expectedParent,
            $expectedClearCache,
            $expectedHandler,
            $expectedExistingData
            ) = $expected;

        $this->assertEquals($expectedClient, $loadConfig->getClient());
        $this->assertEquals($expectedCommandArgs, $loadConfig->getCommandArgs());
        $this->assertEquals($expectedCountCommandArgs, $loadConfig->getCountCommandArgs());
        $this->assertEquals($expectedParent, $loadConfig->getParent());
        $this->assertEquals($expectedClearCache, $loadConfig->doClearCache());
        $this->assertEquals($expectedHandler, $loadConfig->getExceptionHandler());
        $this->assertEquals($expectedExistingData, $loadConfig->getExistingData());
    }
}
