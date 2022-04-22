<?php

namespace Cob\Bundle\ApiServicesBundle\Tests\Unit\Exceptions;

use Cob\Bundle\ApiServicesBundle\Exceptions\UnknownCommandException;
use Cob\Bundle\ApiServicesBundle\Tests\Unit\Models\Response\BaseTestCase;

/**
 * @codeCoverageIgnore
 * @coversDefaultClass \Cob\Bundle\ApiServicesBundle\Exceptions\UnknownCommandException
 */
class UnknownCommandExceptionTest extends BaseTestCase
{
    /**
     * @covers ::__construct
     * @uses \Cob\Bundle\ApiServicesBundle\Models\ServiceClient
     * @uses \Cob\Bundle\ApiServicesBundle\Exceptions\BaseApiServicesBundleException
     */
    public function testConstructor() {
        $badCommand = substr(self::TEST_COMMAND_NAME,1);
        $this->assertEquals(
            'Cannot find command ' . $badCommand . ' in description api_services_bundle.descriptions.test. Did you mean TestCommand, TestRawCommand?',
            (new UnknownCommandException(
                $badCommand,
                $this->getTestDescriptionInstance()
            ))->getMessage()
        );
    }
}
