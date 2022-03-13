<?php

namespace Cob\Bundle\ApiServicesBundle\Tests\Unit\Models;

use Cob\Bundle\ApiServicesBundle\Models\DotData;
use PHPUnit\Framework\TestCase;

/**
 * @codeCoverageIgnore
 * @coversDefaultClass \Cob\Bundle\ApiServicesBundle\Models\DotData
 */
class DotDataTest extends TestCase
{
    const EXPECTED = [
        'one' => 'two',
        'three' => [
            'four' => 'five',
            'six' => 'seven',
            'eight' => [
                'nine' => 'ten',
                'eleven' => 'twelve',
            ]
        ]
    ];

    /**
     * @covers ::__construct
     * @covers ::toArray
     * @covers ::getData
     */
    public function testConstruct()
    {
        $data = new DotData(self::EXPECTED);
        $this->assertSame(self::EXPECTED, $data->toArray());

        $empty = new DotData();
        $this->assertEmpty($empty->toArray());
    }

    /**
     * @covers ::__construct
     * @covers ::getData
     * @covers ::toArray
     * @covers ::setData
     */
    public function testSetData()
    {
        $data = new DotData();
        $data->setData(self::EXPECTED);
        $this->assertSame(self::EXPECTED, $data->toArray());
    }

    /**
     * @covers ::__construct
     * @covers ::getData
     * @covers ::dot
     */
    public function testDot()
    {
        $data = new DotData(self::EXPECTED);

        $this->assertEquals('two', $data->dot('one'));
        $this->assertEquals('five', $data->dot('three.four'));
        $this->assertEquals('ten', $data->dot('three.eight.nine'));

        //We'll call this again to confirm, through our coverage data, cache was used
        $this->assertEquals('ten', $data->dot('three.eight.nine'));

        $this->assertFalse($data->dot('thirteen'));
        $this->assertEquals('blah', $data->dot('thirteen', 'blah'));
    }

    /**
     * @covers ::__construct
     * @covers ::getData
     * @covers ::dot
     */
    public function testEmptyDataReturnsDefault()
    {
        $data = new DotData();

        $this->assertFalse($data->dot('nothing'));
        $this->assertEquals('blah', $data->dot('nothing', 'blah'));
    }

    /**
     * @covers ::__construct
     * @covers ::getData
     * @covers ::dot
     */
    public function testEmptyKeyReturnsAll()
    {
        $data = new DotData(self::EXPECTED);
        $this->assertSame(self::EXPECTED, $data->dot(''));
    }

    /**
     * @covers ::__construct
     * @covers ::getRawData
     * @covers ::setRawData
     */
    public function testRawData()
    {
        $data = new DotData();
        $rawData = 'raw';

        $this->assertNull($data->getRawData());

        $data->setRawData($rawData);
        $this->assertEquals($rawData, $data->getRawData());
    }

    /**
     * @covers ::__construct
     * @covers ::of
     * @covers ::getRawData
     * @covers ::setRawData
     * @covers ::getData
     * @covers ::setData
     * @covers ::dot
     */
    public function testOf()
    {
        $structured = ['foo' => 'bar'];

        $data = DotData::of($structured);

        $this->assertEquals($structured, $data->getData());
        $this->assertEquals('bar', $data->dot('foo'));

        $raw = 'raw';

        $rawData = DotData::of($raw);

        $this->assertEquals($raw, $rawData->getRawData());
    }
}
