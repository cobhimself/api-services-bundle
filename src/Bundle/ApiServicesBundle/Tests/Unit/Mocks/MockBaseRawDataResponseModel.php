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

use Cob\Bundle\ApiServicesBundle\Models\Response\BaseResponseModel;
use Cob\Bundle\ApiServicesBundle\Models\Config\ResponseModelConfig;
use Cob\Bundle\ApiServicesBundle\Models\Config\ResponseModelConfigBuilder;
use Cob\Bundle\ApiServicesBundle\Tests\Unit\Models\Response\BaseResponseModelTestCase;

/**
 * @codeCoverageIgnore
 */
class MockBaseRawDataResponseModel extends BaseResponseModel
{
    protected static function setup(): ResponseModelConfigBuilder
    {
        return ResponseModelConfig::builder()
            ->command(BaseResponseModelTestCase::TEST_COMMAND_NAME)
            ->defaultArgs(BaseResponseModelTestCase::TEST_COMMAND_ARGS)
            ->holdsRawData();
    }
}
