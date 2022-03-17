<?php

namespace Cob\Bundle\ApiServicesBundle\Tests\Unit\Models;

use Cob\Bundle\ApiServicesBundle\Models\CacheProvider;
use Doctrine\Common\Cache\Cache;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;

/**
 * @coversDefaultClass \Cob\Bundle\ApiServicesBundle\Models\CacheProvider
 * @codeCoverageIgnore
 */
class CacheProviderTest extends TestCase {

    /**
     * @var CacheProvider
     */
    private $cacheProvider;

    /**
     * @covers ::__construct
     */
    public function setUp() {
        parent::setUp();

        $this->cacheProvider = new CacheProvider('/tmp', 'whatever');
    }

    /**
     * @covers ::setLifeTime
     * @covers ::clearLifetime
     * @covers ::getLifetime
     */
    public function testSetLifeTimeAndClear()
    {
        $this->cacheProvider->setLifeTime(100);
        $this->assertEquals(100, $this->cacheProvider->getLifetime());
        $this->cacheProvider->clearLifetime();
        $this->assertEquals(0, $this->cacheProvider->getLifetime());
    }

    /**
     * @covers ::setLifeTime
     * @covers ::setLifeTimeMinutes
     * @covers ::getLifetime
     */
    public function testSetLifeTimeMinutes()
    {
        $this->cacheProvider->setLifeTimeMinutes(10);
        $this->assertEquals(10 * 60, $this->cacheProvider->getLifetime());
    }

    /**
     * @covers ::setLifeTime
     * @covers ::setLifeTimeHours
     * @covers ::getLifetime
     */
    public function testSetLifeTimeHours()
    {
        $this->cacheProvider->setLifeTimeHours(2);
        $this->assertEquals(2 * 60 * 60, $this->cacheProvider->getLifetime());
    }

    /**
     * @covers ::setLifeTime
     * @covers ::setLifeTimeDays
     * @covers ::getLifetime
     */
    public function testSetLifeTimeDays()
    {
        $this->cacheProvider->setLifeTimeDays(1);
        $this->assertEquals(1 * 24 * 60 * 60, $this->cacheProvider->getLifetime());
    }

    /**
     * @covers ::save
     * @covers ::warnIfLifeTimeSent
     */
    public function testWarnIfLifeTimeSentOnSave()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Lifetime should not be set directly');
        $this->cacheProvider->save('id', 'data', 100);
    }

    /**
     * @covers ::saveMultiple
     * @covers ::warnIfLifeTimeSent
     */
    public function testWarnIfLifeTimeSentOnSaveMultiple()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Lifetime should not be set directly');
        $this->cacheProvider->saveMultiple(['id' => 'data'], 100);
    }
}
