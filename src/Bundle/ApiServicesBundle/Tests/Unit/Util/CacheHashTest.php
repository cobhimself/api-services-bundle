<?php

namespace Cob\Bundle\ApiServicesBundle\Tests\Unit\Util;

use Cob\Bundle\ApiServicesBundle\Models\Util\CacheHash;
use Cob\Bundle\ApiServicesBundle\Tests\Unit\Mocks\MockBaseResponseModel;
use PHPUnit\Framework\TestCase;

/**
 * @codeCoverageIgnore
 * @coversDefaultClass \Cob\Bundle\ApiServicesBundle\Models\Util\CacheHash
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Config\ResponseModelConfigSharedTrait
 */
class CacheHashTest extends TestCase
{

    /**
     * @covers ::getHashForResponseClassAndArgs
     * @covers ::hashArray
     * @uses \Cob\Bundle\ApiServicesBundle\Models\Response\BaseResponseModel
     * @uses \Cob\Bundle\ApiServicesBundle\Models\Config\ResponseModelConfig
     * @uses \Cob\Bundle\ApiServicesBundle\Models\Util\ClassUtil
     */
    public function testGetHashForSameInputProducesSameOutput()
    {
        $hash1 = CacheHash::getHashForResponseClassAndArgs(
            MockBaseResponseModel::class,
            ['one', 'two', 'three']
        );

        $hash2 = CacheHash::getHashForResponseClassAndArgs(
            MockBaseResponseModel::class,
            ['one', 'two', 'three']
        );

        $this->assertEquals($hash1, $hash2);
    }

    /**
     * @covers ::getHashForResponseClassAndArgs
     * @covers ::hashArray
     * @uses \Cob\Bundle\ApiServicesBundle\Models\Response\BaseResponseModel
     * @uses \Cob\Bundle\ApiServicesBundle\Models\Config\ResponseModelConfig
     * @uses \Cob\Bundle\ApiServicesBundle\Models\Util\ClassUtil
     */
    public function testGetHashForDifferentInputProducesDifferentOutput()
    {
        $hash1 = CacheHash::getHashForResponseClassAndArgs(
            MockBaseResponseModel::class,
            ['one', 'two', 'three']
        );

        $hash2 = CacheHash::getHashForResponseClassAndArgs(
            MockBaseResponseModel::class,
            ['four', 'five', 'six']
        );

        $this->assertNotEquals($hash1, $hash2);
    }
}
