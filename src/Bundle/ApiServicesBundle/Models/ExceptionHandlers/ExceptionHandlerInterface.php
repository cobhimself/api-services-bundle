<?php
/*
 * This file is part of the cobhimself/api-services-bundle package.
 *
 * (c) Collin D. Brooks <collin.brooks@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cob\Bundle\ApiServicesBundle\Models\ExceptionHandlers;

use Throwable;

/**
 * Interface for our exception handlers.
 */
interface ExceptionHandlerInterface
{
    /**
     * Set the exception we are working with.
     */
    public function setException(Throwable $e);

    /**
     * Supply a list of exception fully-qualified class names this handler
     * will handle.
     */
    public function setHandles(array $handles);

    /**
     * Return the list of exception classes this handler handles.
     */
    public function getHandles(): array;

    /**
     * Determine whether or not this handler handles the given exception.
     */
    public function handles(Throwable $e): bool;

    /**
     * Handle the given exception.
     *
     * @param Throwable $e The throwable to handle
     *
     * @return mixed
     */
    public function handle(Throwable $e);

    /**
     * Get the result of this handler.
     *
     * If this handler doesn't handle the exception it's being asked to handle,
     * the result is never returned but the original exception is thrown again.
     *
     * @return mixed
     */
    public function getResult();

    /**
     * Get the original exception this handler was working with.
     *
     * It's possible to override the exception the handler is working with while
     * it is being handled. You can call this method to retrieve the original
     * exception itself.
     */
    public function getOriginalException(): Throwable;

    /**
     * Get the exception we are working with.
     */
    public function getException(): Throwable;

    /**
     * Return whether or not the original exception being handled by this
     * handler was altered in any way.
     */
    public function exceptionAltered(): bool;

    /**
     * Wrap the exception being handled by this handler into another exception
     * provided by `setWrapper`.
     *
     * Wrap functionality only occurs in the following conditions:
     *
     *  - if `setWrapper` has been called
     *  - `passThru` is true after the exception is handled
     *  - if a handler does not handle the exception and a wrapper exists
     *
     * @return mixed
     */
    public function wrap(): Throwable;

    /**
     * Sets a callback to be used to wrap the exception being handled by
     * this handler.
     *
     * Even if an exception is not handled by this handler, if `passThru` is not
     * false, the exception being handled can be wrapped by another exception.
     *
     * @param callable $wrapper function which is sent the exception being
     *                          handled and which should return a
     *                          final exception
     */
    public function setWrapper(callable $wrapper);
}
