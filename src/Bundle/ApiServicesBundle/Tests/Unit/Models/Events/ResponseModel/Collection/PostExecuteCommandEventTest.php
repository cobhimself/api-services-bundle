<?php

namespace Cob\Bundle\ApiServicesBundle\Tests\Unit\Models\Events\ResponseModel\Collection;

use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\PostExecuteCommandEvent;
use Cob\Bundle\ApiServicesBundle\Tests\Unit\Mocks\PersonCollection;
use GuzzleHttp\Command\Command;
use PHPUnit\Framework\TestCase;

/**
 * @codeCoverageIgnore
 * @coversDefaultClass \Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\PostExecuteCommandEvent
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Response\Collection\BaseResponseModelCollection
 * @covers \Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\ResponseModelCollectionEvent
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Config\ResponseModelCollectionConfig
 * @uses \CoB\Bundle\ApiServicesBundle\Models\Config\ResponseModelConfigSharedTrait
 */
class PostExecuteCommandEventTest extends TestCase
{
    /**
     * @covers ::__construct
     * @covers ::getConfig
     * @covers ::getCommand
     * @covers ::getResponse
     * @covers ::setResponse
     */
    public function testGettersAndSetters()
    {
        $config = PersonCollection::getConfig();
        $command = new Command($config->getCommand(), $config->getDefaultArgs());
        $response = ['foo' => 'bar'];
        $responseNew = ['boo' => 'baz'];

        $event = new PostExecuteCommandEvent($config, $command, $response);

        $this->assertEquals($config, $event->getConfig());
        $this->assertEquals($command, $event->getCommand());
        $this->assertEquals($response, $event->getResponse());

        $event->setResponse($responseNew);
        $this->assertEquals($responseNew, $event->getResponse());
    }
}
