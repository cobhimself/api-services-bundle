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
use Cob\Bundle\ApiServicesBundle\Tests\ServiceClientMockTrait;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * Class AbstractResponseModelTest
 *
 * @package Cob\Bundle\ApiServicesBundle\Tests\Models
 * @codeCoverageIgnore
 * @covers \Cob\Bundle\ApiServicesBundle\Models\AbstractResponseModel
 * @coversDefaultClass \Cob\Bundle\ApiServicesBundle\Models\AbstractResponseModel
 *
 * @uses \Cob\Bundle\ApiServicesBundle\Models\ResponseModelSetupTrait
 * @uses \Cob\Bundle\ApiServicesBundle\Models\ServiceClient
 * @uses \Cob\Bundle\ApiServicesBundle\Exceptions\BaseApiServicesBundleException
 */
class AbstractResponseModelTest extends TestCase
{
    use ServiceClientMockTrait;

    /**
     * @var MockResponseModel
     */
    private $mock;

    public function setUp()
    {
        $this->mock = new MockResponseModel();
    }

    /**
     * @param array $data
     * @param null  $parent
     * @param null  $client
     *
     * @dataProvider dataProviderForTestConstruct
     *
     * @uses \Cob\Bundle\ApiServicesBundle\Models\AbstractResponseModel::dispatchEvent()
     * @uses \Cob\Bundle\ApiServicesBundle\Models\Subscribers\ProgressTrait::getOutput()
     * @uses \Cob\Bundle\ApiServicesBundle\Models\Subscribers\ProgressTrait::getIo()
     * @uses \Cob\Bundle\ApiServicesBundle\Models\Subscribers\ProgressTrait::getProgressBar()
     * @uses \Cob\Bundle\ApiServicesBundle\Models\Subscribers\ProgressTrait::inheritOutputFrom()
     * @covers \Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\AssociateParentModelEvent
     * @covers \Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\ResponseModelEvent
     * @covers ::__construct
     * @covers ::setClient
     * @covers ::getClient
     * @covers ::onAssociateParent
     * @covers ::setParent
     * @covers ::doSetParent
     * @covers ::getData
     * @covers ::getParent
     */
    public function testConstruct(array $data, $parent = null, $client = null)
    {
        $mock = new MockResponseModel($data, $parent, $client);
        self::assertSame(['one' => 1, 'two' => 2], $mock->getData());
        self::assertSame($parent, $mock->getParent());
        self::assertSame($client, $mock->getClient());
    }

    public function dataProviderForTestConstruct()
    {
        $goodParent = new MockResponseModel();
        $badParentModel = new stdClass();
        $serviceClient = $this->getServiceClientMock([]);

        return [
            [
                'data' => ['one' => 1, 'two' => 2],
                'parent' => $goodParent,
                $serviceClient,
            ]
        ];
    }

    /**
     * @param      $mock
     * @param      $method
     * @param null $expectedValue
     * @param null $expectException
     * @param null $missingConst
     *
     * @dataProvider dataProviderForTestConstants
     *
     * @covers ::getLoadCommand
     * @covers ::getLoadArguments
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
}
