<?php

namespace Cob\Bundle\ApiServicesBundle\Tests\Unit\Exceptions;

use Cob\Bundle\ApiServicesBundle\Exceptions\ResponseModelLoadCancelledException;
use Cob\Bundle\ApiServicesBundle\Tests\Unit\Mocks\MockBaseResponseModel;
use Cob\Bundle\ApiServicesBundle\Tests\Unit\Models\Response\BaseTestCase;

/**
 * @codeCoverageIgnore
 * @coversDefaultClass \Cob\Bundle\ApiServicesBundle\Exceptions\ResponseModelLoadCancelledException
 * @uses \Cob\Bundle\ApiServicesBundle\Exceptions\BaseApiServicesBundleException
 * @uses \Cob\Bundle\ApiServicesBundle\Exceptions\ResponseModelSetupException
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Config\ResponseModelConfig
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Config\ResponseModelConfigBuilder
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Config\ResponseModelConfigSharedTrait
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Deserializer
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Loader\AbstractLoader
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Loader\AsyncLoader
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Loader\Config\LoadConfig
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Loader\Config\LoadConfigBuilder
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Loader\Config\LoadConfigSharedTrait
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Loader\LoadState
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Response\BaseResponseModel
 * @uses \Cob\Bundle\ApiServicesBundle\Models\ServiceClient
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Util\ClassUtil
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Util\LogUtil
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Util\Promise
 * @uses \Cob\Bundle\ApiServicesBundle\Models\HasOutputTrait
 */
class ResponseModelLoadCancelledExceptionTest extends BaseTestCase
{
    /**
     * @covers ::__construct
     */
    public function testConstruct()
    {
        $model = MockBaseResponseModel::using($this->getServiceClientMock([]))->loadAsync();
        $reason = 'blah';
        $exception = new ResponseModelLoadCancelledException(
            $model,
            self::TEST_COMMAND_ARGS,
            true,
            $reason
        );

        $expected = [];
        $expected[] = 'Loading of ' . MockBaseResponseModel::class . ' was cancelled!';
        $expected[] = ' > Command Args: ["arg1","arg2"]';
        $expected[] = ' > Clear Cache: true';
        $expected[] = ' > Reason: blah';

        $this->assertEquals(join(PHP_EOL, $expected) . PHP_EOL, $exception->getMessage());
    }
}
