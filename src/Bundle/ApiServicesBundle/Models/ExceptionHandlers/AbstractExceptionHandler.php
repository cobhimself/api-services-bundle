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

use Cob\Bundle\ApiServicesBundle\Exceptions\BaseApiServicesBundleException;
use Throwable;

/**
 * Abstract class which all exception handlers are based.
 *
 * An exception handler is given an exception and determines programmatically
 * how the exception should be handled. Should we swallow it and keep going?
 * Wrap it in another exception? Ignore it? Pass it through untouched?
 */
abstract class AbstractExceptionHandler implements ExceptionHandlerInterface
{
    /**
     * The value which represents "all exceptions"
     */
    const ALL = '*';

    /**
     * The exception we are working with.
     *
     * Initially, this is the exception provided to the handler. However, there
     * is no reason the handler cannot modify the exception (either set it to a
     * different exception all together or build onto it).
     *
     * @var Throwable
     */
    private $originalException;

    /**
     * This exception is the "working exception". It can be changed during the
     * handling of the exception and is returned when asking for the exception
     * being handled by the handler.
     *
     * @var Throwable
     */
    private $exception;

    /**
     * Keeps track of whether or not we want to pass our exception on.
     *
     * @var bool
     */
    private $passThru = false;

    /**
     * @var array list of exception classes this handler will handle. If
     *            self::ALL is in the list, all exceptions are handled.
     */
    private $handles = [];

    /**
     * @var mixed the ultimate result of this handler
     */
    private $result;

    /**
     * A function used to wrap the exception handled by this handler into
     * another exception.
     *
     * If not set, or `passThru` is false, no wrapping occurs.
     *
     * @var callable
     */
    private $wrapper;

    /**
     * Set the exception classes this handler will handle.
     *
     * @param array|null $handles if null, and `setHandles` is never called,
     *                            the handler won't handle anything and the
     *                            exception will pass through (and optionally be
     *                            wrapped). If self::ALL is in the list of
     *                            exceptions, all exceptions are handled.
     */
    public function __construct(array $handles = null)
    {
        if (null !== $handles) {
            $this->setHandles($handles);
        }
    }

    /**
     * @inheritDoc
     */
    public function setException(Throwable $e)
    {
        //Set our originalException property so we can always refer to the
        //original exception.
        if (!$this->originalException) {
            $this->originalException = $e;
        }

        $this->exception = $e;
    }

    /**
     * @inheritDoc
     */
    public function getOriginalException(): Throwable
    {
        return $this->originalException;
    }

    /**
     * @inheritDoc
     */
    public function getException(): Throwable
    {
        return $this->exception;
    }

    /**
     * @inheritDoc
     */
    public function exceptionAltered(): bool
    {
        return $this->exception !== $this->originalException;
    }

    /**
     * @inheritDoc
     */
    public function setHandles(array $handles)
    {
        $this->handles = $handles;
    }

    /**
     * @inheritDoc
     */
    public function getHandles(): array
    {
        return $this->handles;
    }

    /**
     * @inheritDoc
     */
    public function handles(Throwable $e): bool
    {
        $handles = $this->getHandles();

        return in_array(static::ALL, $handles, true)
            || in_array(get_class($e), $handles, true);
    }

    /**
     * Specify whether or not this handler will end up passing an exception on.
     *
     * NOTE: If we do not wrap the exception, this handler passes the exception
     * on as-is.
     *
     * @param bool $passThru if true, the exception being handled is
     *                       thrown again
     */
    public function setPassThru(bool $passThru)
    {
        $this->passThru = $passThru;
    }

    /**
     * Get whether or not we are going to pass the exception on.
     */
    public function getPassThru(): bool
    {
        return $this->passThru;
    }

    /**
     * Set the result of this handler.
     *
     * If a handler determines it'd like to ignore the exception sent in and
     * return `null` for example as a result, it can do so using this method.
     * Alternatively, nothing stops you from setting the result to any other
     * value... like 'monkey' or something. The power is yours.
     *
     * @param mixed $result
     */
    protected function setResult($result)
    {
        $this->result = $result;
    }

    /**
     * @inheritDoc
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * Run before handling the exception.
     *
     * @throws Throwable if this handler does not handle the given exception
     */
    protected function preHandle(Throwable $e)
    {
        $this->setException($e);

        //We don't handle this exception...
        if (!$this->handles($e)) {
            //...but we allow our handler to wrap the exception
            //if we have a wrapper.
            throw $this->wrap();
        }
    }

    /**
     * Run after the handler handles the given exception.
     *
     * @throws Throwable if it's been determined our handler should pass on
     *                   the exception
     */
    protected function postHandle()
    {
        if ($this->passThru) {
            throw $this->wrap();
        }
    }

    /**
     * @inheritDoc
     *
     * @throws Throwable
     */
    public function handle(Throwable $e)
    {
        $this->preHandle($e);
        $this->doHandle();
        $this->postHandle();

        return $this->getResult();
    }

    /**
     * @inheritDoc
     */
    public function wrap(): Throwable
    {
        $result = $this->getException();

        if (is_callable($this->wrapper)) {
            $wrapper = $this->wrapper;
            $result = $wrapper($result);

            if (!($result instanceof Throwable)) {
                $result = new BaseApiServicesBundleException(
                    'Unable to wrap a handled exception because the wrapper function did not return an exception!',
                    $this->getException()
                );
            }
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function setWrapper(callable $wrapper)
    {
        $this->wrapper = $wrapper;
    }

    /**
     * Perform handling of the exception given to this handler.
     *
     * To be overwritten by extending classes.
     */
    protected function doHandle()
    {
        //Do nothing by default
    }

    /**
     * Return a default exception handler.
     *
     * This handler will ignore all exceptions and have the final result be
     * null. This basically swallows all exceptions. You can use the
     * `setWrapper` method to specify how this exception is wrapped.
     *
     * @return static
     */
    public static function ignore(): ExceptionHandlerInterface
    {
        $handler = new static();
        $handler->setHandles([self::ALL]);
        $handler->setResult(null);

        return $handler;
    }

    /**
     * Get an exception handler that allows the exceptions this handler cares
     * about to pass through and be thrown again.
     *
     * Without a wrapper set, this ultimately makes it so the handler is just a
     * tunnel for the original exception.
     *
     * @return static
     */
    public static function passThru(): ExceptionHandlerInterface
    {
        $handler = new static();
        $handler->setPassThru(true);

        return $handler;
    }

    /**
     * Wrap any exception encountered with the given exceptionClass.
     *
     * The exception class MUST have the final argument to its constructor be
     * the `previous` exception parameter. Any arguments sent in MUST NOT
     * include the `previous` exception parameter as that is
     * added automatically.
     *
     * @param string $exceptionClass     the exception class to wrap any caught
     *                                   exception within
     * @param array  $exceptionArguments arguments to send to our wrapping
     *                                   exception class minus the `previous`
     *                                   exception parameter
     */
    public static function passThruAndWrapWith(
        string $exceptionClass,
        array $exceptionArguments = []
    ): ExceptionHandlerInterface {
        //Our handler will pass through all exceptions
        $handler = static::passThru();

        //..but will wrap the exception
        $handler->setWrapper(
            static function (Throwable $e) use (
                $exceptionClass, $exceptionArguments
            ) {
                //Add our previous exception as our final exception argument
                $exceptionArguments[] = $e;

                return new $exceptionClass(...$exceptionArguments);
            }
        );

        return $handler;
    }
}
