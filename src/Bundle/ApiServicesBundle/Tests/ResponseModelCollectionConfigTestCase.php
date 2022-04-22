<?php

namespace Cob\Bundle\ApiServicesBundle\Tests;

use Cob\Bundle\ApiServicesBundle\Models\Config\ResponseModelCollectionConfig;
use Cob\Bundle\ApiServicesBundle\Models\ExceptionHandlers\ExceptionHandlerInterface;
use Cob\Bundle\ApiServicesBundle\Tests\Unit\Mocks\Person;
use Cob\Bundle\ApiServicesBundle\Tests\Unit\Mocks\PersonCollection;
use Cob\Bundle\ApiServicesBundle\Tests\Unit\Models\Response\BaseTestCase;

/**
 * @codeCoverageIgnore
 */
class ResponseModelCollectionConfigTestCase extends BaseTestCase {

    public function confirmDefaults(ResponseModelCollectionConfig $config)
    {
        $this->assertEquals(PersonCollection::class, $config->getResponseModelClass());
        $this->assertEquals(Person::class, $config->getChildResponseModelClass());
        $this->assertEquals('', $config->getCommand());
        $this->assertEquals([], $config->getDefaultArgs());
        $this->assertEquals('', $config->getCollectionPath());
        $this->assertNull($config->getCountCommand());
        $this->assertEquals([], $config->getCountArgs());
        $this->assertEquals('', $config->getCountValuePath());
        $this->assertEquals(
            ResponseModelCollectionConfig::LOAD_MAX_RESULTS_DEFAULT,
            $config->getLoadMaxResults()
        );
        $this->assertFalse($config->hasBuildCountArgsCallback());
        $this->assertEquals(
            ResponseModelCollectionConfig::CHUNK_COMMAND_MAX_RESULTS_DEFAULT,
            $config->getChunkCommandMaxResults()
        );
        $this->assertEquals([], $config->getInitCallbacks());
        $this->assertInstanceOf(
            ExceptionHandlerInterface::class,
            $config->getDefaultExceptionHandler()
        );
    }
}
