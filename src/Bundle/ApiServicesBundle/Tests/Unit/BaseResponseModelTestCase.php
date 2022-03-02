<?php

namespace Cob\Bundle\ApiServicesBundle\Tests\Unit;

use Cob\Bundle\ApiServicesBundle\Tests\ServiceClientMockTrait;
use Cob\Bundle\ApiServicesBundle\Tests\Unit\Mocks\MockBaseResponseModel;
use PHPUnit\Framework\TestCase;

/**
 * @codeCoverageIgnore
 */
abstract class BaseResponseModelTestCase extends TestCase
{
    use ServiceClientMockTrait;

    const TEST_COMMAND_NAME = 'TestCommand';
    const TEST_COMMAND_ARGS = ['arg1', 'arg2'];
    const MOCK_INNER_RESPONSE_DATA = ['one' => 1, 'two' => 2];
    const MOCK_RESPONSE_DATA = ['data' => self::MOCK_INNER_RESPONSE_DATA];

    protected function getMockParentModel()
    {
        //Normally, the parent model would be a different type of model but this will do fine for testing we can
        //get a parent model
        static $mockParentModel;

        if (is_null($mockParentModel)) {
            $mockParentModel = MockBaseResponseModel::withData($this->getServiceClientMock(), []);
        }

        return $mockParentModel;
    }

}