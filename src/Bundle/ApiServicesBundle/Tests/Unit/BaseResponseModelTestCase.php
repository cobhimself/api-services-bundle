<?php

namespace Cob\Bundle\ApiServicesBundle\Tests\Unit;

use PHPUnit\Framework\TestCase;

abstract class BaseResponseModelTestCase extends TestCase
{
    const TEST_COMMAND_NAME = 'TestCommand';
    const TEST_COMMAND_ARGS = ['arg1', 'arg2'];
    const MOCK_INNER_RESPONSE_DATA = ['one' => 1, 'two' => 2];
    const MOCK_RESPONSE_DATA = ['data' => self::MOCK_INNER_RESPONSE_DATA];

}