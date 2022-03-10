<?php

namespace Cob\Bundle\ApiServicesBundle\Tests\Unit\Models\Loaders;

use Cob\Bundle\ApiServicesBundle\Models\Loader\LoadState;
use PHPUnit\Framework\TestCase;

/**
 * @codeCoverageIgnore
 * @coversDefaultClass \Cob\Bundle\ApiServicesBundle\Models\Loader\LoadState
 */
class LoadStateTest extends TestCase
{
    /**
     * @covers ::__construct
     * @covers ::getState
     * @covers ::isState
     * @covers ::waiting
     * @covers ::loaded
     * @covers ::isWaiting
     */
    public function testWaiting()
    {
        $this->assertTrue(LoadState::waiting()->isWaiting());
        $this->assertFalse(LoadState::loaded()->isWaiting());
    }

    /**
     * @covers ::__construct
     * @covers ::getState
     * @covers ::isState
     * @covers ::loaded
     * @covers ::loadedWithData
     * @covers ::isLoaded
     */
    public function testLoaded()
    {
        $this->assertTrue(LoadState::loaded()->isLoaded());
        $this->assertFalse(LoadState::loadedWithData()->isLoaded());
    }

    /**
     * @covers ::__construct
     * @covers ::getState
     * @covers ::isState
     * @covers ::loadedWithData
     * @covers ::waiting
     * @covers ::isLoadedWithData
     */
    public function testLoadedWithData()
    {
        $this->assertTrue(LoadState::loadedWithData()->isLoadedWithData());
        $this->assertFalse(LoadState::waiting()->isLoadedWithData());
    }
}