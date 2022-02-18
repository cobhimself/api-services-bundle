<?php

/*
 * This file is part of the cobhimself/api-services-bundle package.
 *
 * (c) Collin D. Brooks <collin.brooks@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cob\Bundle\ApiServicesBundle\Tests\Unit\Mocks;

use Cob\Bundle\ApiServicesBundle\Models\BaseResponseModel;
use Cob\Bundle\ApiServicesBundle\Models\ResponseModelConfig;
use Cob\Bundle\ApiServicesBundle\Tests\Unit\BaseResponseModelTestCase;
use Exception;

/**
 * @codeCoverageIgnore
 */
class MockBaseResponseModelWithInit extends BaseResponseModel
{
    const EXPECTED_EXCEPTION_MSG = 'testing init callback functionality';
    protected static function setup(): ResponseModelConfig
    {
        $config = new ResponseModelConfig(
            BaseResponseModelTestCase::TEST_COMMAND_NAME,
            BaseResponseModelTestCase::TEST_COMMAND_ARGS
        );

        $config->addInitCallback(function(MockBaseResponseModelWithInit $me) {
            throw new Exception(self::EXPECTED_EXCEPTION_MSG);
        });

        return $config;
    }
}