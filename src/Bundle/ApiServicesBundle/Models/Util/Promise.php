<?php
/*
 * This file is part of the cobhimself/api-services-bundle package.
 *
 * (c) Collin D. Brooks <collin.brooks@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cob\Bundle\ApiServicesBundle\Models\Util;

use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\PostRunAllPromisesEvent;
use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\PostRunPromiseInAllEvent;
use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\PreRunAllPromisesEvent;
use Cob\Bundle\ApiServicesBundle\Models\ServiceClient;
use Exception;
use GuzzleHttp\Promise\Each;
use GuzzleHttp\Promise\Promise as GuzzlePromise;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Promise\RejectedPromise;

/**
 * A collection of methods which make it easier to create and run promises.
 */
class Promise
{
    /**
     * Perform an async operation.
     *
     * Guzzle's Promise object requires a reference to the promise itself if you
     * are to resolve the promise in its wait callback. Passing in &$promise all
     * the time in order to resolve the promise requires unnecessary fluff in
     * the code. The returned promise is automatically resolved with the value
     * from the callable function's return value.
     *
     * The returned promise MUST have its `wait()` method run in order for the
     * async operation to begin.
     *
     * If an exception occurs within the provided callable, the promise is rejected
     * with the exception as the reason.
     *
     * @param callable $callable callable whose return value will resolve (or reject)
     *                           the created promise when it is waited on
     *
     * @return PromiseInterface|GuzzlePromise|RejectedPromise the promise
     */
    public static function async(callable $callable): PromiseInterface
    {
        /** @noinspection PhpUnnecessaryLocalVariableInspection */
        $promise = new GuzzlePromise(function () use ($callable, &$promise) {
            try {
                $promise->resolve($callable());
            } catch (Exception $e) {
                $promise->reject($e);
            }
        });

        return $promise;
    }

    /**
     * Create an aggregate set of promises which can be waited on.
     *
     * If a ServiceClient is provided, the promises are wrapped between
     * dispatched events and, when a promise is fulfilled, an event is
     * dispatched with data relating to the promise.
     *
     * Ensures no promise is rejected. If any promise is rejected, then the
     * aggregate promise is rejected with the encountered rejection.
     *
     * @param array         $promises    the promises to run
     * @param mixed         $message     the message to provide our pre-run
     *                                   event; can be an object which the
     *                                   dispatched events can use however
     * @param ServiceClient $client      the service client whose dispatcher
     *                                   will be used to dispatch the events
     *
     * @param int           $concurrency how many promises to run at a time
     *
     * @return GuzzlePromise|PromiseInterface|RejectedPromise
     */
    public static function all(
        array $promises,
        $message = null,
        ServiceClient $client = null,
        int $concurrency = 25
    ) {
        return self::async(function () use (
            $client,
            $message,
            $promises,
            $concurrency
        ) {
            $totalPromises = count($promises);

            if (null !== $client) {
                $client->dispatchEvent(
                    PreRunAllPromisesEvent::class,
                    $totalPromises,
                    $message
                );
            }

            return Each::ofLimitAll(
                $promises,
                $concurrency,
                function ($value, $index) use (
                    $client,
                    $totalPromises,
                    $message
                ) {
                    if (null !== $client) {
                        $client->dispatchEvent(
                            PostRunPromiseInAllEvent::class,
                            $value,
                            $index,
                            $totalPromises,
                            $message
                        );
                    }
                }
            )->then(function () use ($message, $client) {
                if (null !== $client) {
                    $client->dispatchEvent(
                        PostRunAllPromisesEvent::class,
                        $message
                    );
                }
            })->wait();
        });
    }
}
