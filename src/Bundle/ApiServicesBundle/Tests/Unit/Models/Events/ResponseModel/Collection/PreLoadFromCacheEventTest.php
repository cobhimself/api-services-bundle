<?php

namespace Cob\Bundle\ApiServicesBundle\Tests\Unit\Models\Events\ResponseModel\Collection;

use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\CommandFulfilledEvent;
use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\PreLoadEvent;
use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\PreLoadFromCacheEvent;
use Cob\Bundle\ApiServicesBundle\Tests\Unit\Mocks\PersonCollection;
use GuzzleHttp\Command\Command;
use GuzzleHttp\Promise\FulfilledPromise;
use PHPUnit\Framework\TestCase;

/**
 * @codeCoverageIgnore
 * @coversDefaultClass \Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\PreLoadFromCacheEvent
 * @uses \Cob\Bundle\ApiServicesBundle\Models\BaseResponseModelCollection
 * @covers \Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\ResponseModelCollectionEvent
 * @uses \Cob\Bundle\ApiServicesBundle\Models\ResponseModelCollectionConfig
 * @uses \CoB\Bundle\ApiServicesBundle\Models\ResponseModelConfigSharedTrait
 */
class PreLoadFromCacheEventTest extends TestCase
{
    /**
     * @covers ::__construct
     * @covers ::getConfig
     * @covers ::getHash
     * @covers ::setHash
     */
    public function testGettersAndSetters()
    {
        $config = PersonCollection::getConfig();
        $hash = '1234';
        $hashNew = '5678';

        $event = new PreLoadFromCacheEvent($config, $hash);

        $this->assertEquals($config, $event->getConfig());
        $this->assertEquals($hash, $event->getHash());

        $event->setHash($hashNew);
        $this->assertEquals($hashNew, $event->getHash());
    }
}