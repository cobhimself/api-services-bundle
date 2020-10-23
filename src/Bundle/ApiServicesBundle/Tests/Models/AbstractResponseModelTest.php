<?php

/*
 * This file is part of the cobhimself/api-services-bundle package.
 *
 * (c) Collin D. Brooks <collin.brooks@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cob\Bundle\ApiServicesBundle\Tests\Models;

use Cob\Bundle\ApiServicesBundle\Exceptions\ResponseModelSetupException;
use Cob\Bundle\ApiServicesBundle\Tests\Mocks\BadMockResponseModel;
use Cob\Bundle\ApiServicesBundle\Tests\Mocks\MockResponseModel;
use PHPUnit\Framework\TestCase;

class AbstractResponseModelTest extends TestCase
{
    /**
     * @var MockResponseModel
     */
    private $mock;

    public function setUp()
    {
        $this->mock = new MockResponseModel();
    }

    /**
     * @param      $mock
     * @param      $method
     * @param null $expectedValue
     * @param null $expectException
     * @param null $missingConst
     *
     * @dataProvider dataProviderForTestConstants
     */
    public function testConstants(
        $mock,
        $method,
        $expectedValue = null,
        $expectException = null,
        $missingConst = null
    ) {
        if ($expectException) {
            $this->expectException(ResponseModelSetupException::class);
            $this->expectExceptionMessage('Please set ' . $missingConst);
        }

        $value = $mock::$method();

        self::assertEquals($expectedValue, $value);
    }

    public function dataProviderForTestConstants()
    {
        $mocks = [
            MockResponseModel::class => false,
            BadMockResponseModel::class => true
        ];

        $methodMap = [
            'LOAD_COMMAND' => 'getLoadCommand',
            'LOAD_ARGUMENTS' => 'getLoadArguments',
        ];

        $expectations = [
            'LOAD_COMMAND' => 'TestCommand',
            'LOAD_ARGUMENTS' => ['arg1', 'arg2'],
        ];

        $data = [];

        foreach($mocks as $mockClass => $expectException) {
            foreach($expectations as $const => $value) {
                $method = $methodMap[$const];
                if ($expectException) {
                    $value = null;
                }
                $args = [$mockClass, $method, $value, $expectException, $const];
                $dataKey = ($expectException ? 'No Exception for ' : 'Exception for ') . $method . ' === ' . $const;
                $data[$dataKey] = $args;
            }
        }

        return $data;
    }

    public function testGetLoadCommand()
    {
        self::assertSame(
            MockResponseModel::LOAD_COMMAND,
            MockResponseModel::getLoadCommand()
        );
    }

    public function testGetLoadCommandFails()
    {
        $this->expectException(ResponseModelSetupException::class);
        $this->expectExceptionMessage('Please set LOAD_COMMAND');
        BadMockResponseModel::getLoadCommand();
    }
}
