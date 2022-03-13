<?php

namespace Cob\Bundle\ApiServicesBundle\Tests\Unit\Util;

use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\PostRunAllPromisesEvent;
use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\PostRunPromiseInAllEvent;
use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\PreRunAllPromisesEvent;
use Cob\Bundle\ApiServicesBundle\Models\Util\Promise;
use Cob\Bundle\ApiServicesBundle\Tests\ServiceClientMockTrait;
use Exception;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * @codeCoverageIgnore
 * @coversDefaultClass \Cob\Bundle\ApiServicesBundle\Models\Util\Promise
 * @uses \Cob\Bundle\ApiServicesBundle\Models\Deserializer
 * @uses \Cob\Bundle\ApiServicesBundle\Models\ServiceClient
 */
class PromiseTest extends TestCase {
    use ServiceClientMockTrait;

    /**
     * @covers ::async
     */
    public function testAsyncCallDelayed()
    {
        $actual = 'whatever';

        $promise = Promise::async(function () use (&$actual) {
            $actual = 'changed';
        });

        $this->assertEquals('pending', $promise->getState());
        $this->assertEquals('whatever', $actual);
        $promise->wait();
        $this->assertEquals('changed', $actual);
    }

    /**
     * @covers ::async
     */
    public function testAsyncRejected()
    {
        $actual = 'whatever';
        $exceptionMessage = "purposely throwing to reject promise";

        $promise = Promise::async(function() use ($exceptionMessage) {
            throw new RuntimeException($exceptionMessage);
        })->otherwise(function (Exception $reason) use (&$actual, $exceptionMessage) {
            $actual = 'changed';
            $this->assertEquals($exceptionMessage, $reason->getMessage());
        });

        $this->assertEquals('pending', $promise->getState());
        $this->assertEquals('whatever', $actual);
        $promise->wait();

        $this->assertEquals('changed', $actual);
        $this->assertEquals('fulfilled', $promise->getState());
    }

    /**
     * @covers ::async
     * @covers ::all
     * @covers \Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\PostRunPromiseInAllEvent
     * @covers \Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\PreRunAllPromisesEvent
     * @covers \Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\PromiseEvent
     */
    public function testAll()
    {
        $client = $this->getServiceClientMock();
        $promises = [];
        $valuesToCheck = [];
        $context = 'my event context';
        $collectionSize = 10;

        //Build up some promises for us to run
        for ($i = 0; $i < $collectionSize; $i++) {

            //We'll set the values for us to check to be a "before" value
            $valuesToCheck[$i] = 'before';

            $promises[] = Promise::async(function () use ($i, &$valuesToCheck) {
                //We'll confirm later the "before" value is now changed to "after"
                $valuesToCheck[$i] = 'after' . $i;
                return $valuesToCheck[$i];
            });

            //No changes should have been made at this time
            $this->assertEquals('before', $valuesToCheck[$i]);
        }

        //Let's confirm our pre run event works as expected
        $client->getDispatcher()->addListener(
            PreRunAllPromisesEvent::NAME,
            function (PreRunAllPromisesEvent $event) use ($context, $collectionSize) {
                $this->assertEquals($collectionSize, $event->getNumItems());
                $this->assertEquals($context, $event->getContext());
            }
        );

        $promisesSeen = array_fill(0, $collectionSize, false);

        //Confirm each individual promise was processed successfully
        $client->getDispatcher()->addListener(
            PostRunPromiseInAllEvent::NAME,
            function (PostRunPromiseInAllEvent $event) use (&$promisesSeen, $collectionSize, $context) {
                $index = $event->getIndex();
                $promisesSeen[$index] = true;
                $this->assertEquals('after' . $index, $event->getValue());
                $this->assertEquals($collectionSize, $event->getCollectionSize());
                $this->assertEquals($context, $event->getContext());
            }
        );

        $client->getDispatcher()->addListener(
            PostRunAllPromisesEvent::NAME,
            function (PostRunAllPromisesEvent $event) use ($context) {
                $this->assertEquals($context, $event->getContext());
            }
        );

        $aggregate = Promise::all($promises, $context, $client);

        $this->assertEquals('pending', $aggregate->getState());
        $this->assertCount($collectionSize,
            array_filter($promisesSeen, function ($item) {
                return $item === false;
            })
        );

        //Wait on all of the promises to fulfill
        $aggregate->wait();

        $this->assertEquals('fulfilled', $aggregate->getState());
        $this->assertCount($collectionSize,
            array_filter($promisesSeen, function ($item) {
                return $item === true;
            })
        );
    }
}
