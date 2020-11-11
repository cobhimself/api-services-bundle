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

use Exception;
use GuzzleHttp\Command\CommandInterface;
use GuzzleHttp\Command\Exception\CommandClientException;
use GuzzleHttp\Command\Exception\CommandException;
use GuzzleHttp\Command\Exception\CommandServerException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * This handler is used to handle service client exceptions thrown during a
 * request for data.
 *
 * It is most helpful when dealing with HTTP Response codes.
 *
 * @method getOriginalException() : CommandException
 */
class ClientCommandExceptionHandler extends AbstractExceptionHandler
{
    /**
     * @var int[] a set of HTTP response codes which should throw an exception
     */
    protected $failOnCodes;

    /**
     * @var callable[] a set of callables keyed by HTTP Response codes and whose
     *                 functionality should determine what to do with the code
     */
    protected $codeCallables = [];

    /**
     * By default, our handler handles the Guzzle command exceptions
     */
    public function __construct()
    {
        parent::__construct([
            CommandClientException::class,
            CommandServerException::class,
        ]);
    }

    /**
     * @return ResponseInterface|null
     */
    public function getResponse()
    {
        return $this->getOriginalException()->getResponse();
    }

    /**
     * @return RequestInterface|null
     */
    public function getRequest()
    {
        return $this->getOriginalException()->getRequest();
    }

    /**
     * Get the command associated with the original exception.
     *
     * @return CommandInterface
     */
    public function getCommand(): CommandInterface
    {
        return $this->getOriginalException()->getCommand();
    }

    /**
     * Get the HTTP Response code from the response.
     *
     * @return int|null
     */
    public function getResponseCode()
    {
        return (null !== $this->getResponse())
            ? $this->getResponse()->getStatusCode()
            : null;
    }

    /**
     * Set a callback to be called upon an HTTP Response code exception by our
     * service client when running a command.
     *
     * The callback function will be sent into the exception to handle as its
     * first argument. If the callback returns a Response object, the exception
     * is ignored and the Response is returned by the `handle` method. Any other
     * return type is ignored and execution of the `handle` method continues
     * (this is most helpful when wanting to set the `passThru` value so the
     * original exception is thrown).
     *
     * @param int      $code     response code associated with the
     *                           given callable
     * @param callable $callable function run when an exception extending
     *                           CommandException is encountered for the
     *                           given code
     *
     * @see CommandException
     * @see CommandClientException
     * @see CommandServerException
     */
    public function setCallbackForCode(int $code, callable $callable)
    {
        $this->codeCallables[$code] = $callable;
    }

    /**
     * Return an exception handler that allows the exception to pass through if
     * the response code exists in the given codes.
     *
     * If <code>$codes</code> is <code>[400, 404]</code>, any
     * <code>CommandException</code> with a response that has either of those
     * two codes would allow the original exception to be passed through
     * and thrown.
     *
     * @param array $codes An array of http response codes
     */
    public static function passThruCodes(array $codes)
    {
        $handler = new self();

        //If a given code is encountered, force this handler to pass the
        //exception on
        $passThru = function () {
            $this->setPassThru(true);
        };

        foreach ($codes as $code) {
            $handler->setCallbackForCode($code, $passThru);
        }
    }

    /**
     * Return a handler which ignores any exception with a response code within
     * the codes list.
     *
     * @param array $codes list of HTTP Response codes for our handler to ignore
     *
     * If <code>$codes</code> is <code>[400, 404]</code>, any
     * <code>CommandException</code> with a response that has either of those
     * two codes would ignore the original exception and set the result of the
     * handler to null.
     *
     * @return ClientCommandExceptionHandler
     */
    public static function ignoreForCodes(
        array $codes
    ): ClientCommandExceptionHandler {
        $handler = new static();
        //By default, we'll pass our exceptions on
        $handler->setPassThru(true);

        //However, if a code in our codes list is encountered, we'll not pass
        //it on and ignore it instead
        $passThru = static function () use ($handler) {
            $handler->setPassThru(false);
        };

        foreach ($codes as $code) {
            $handler->setCallbackForCode($code, $passThru);
        }

        return $handler;
    }

    /**
     * Handle the given exception.
     *
     * This method makes calls to callbacks associated with registered response
     * codes. If the callback returns an object which implements
     * ResponseInterface, the response is set as this handler's result. If the
     * callback returns any other type, a result is not set.
     *
     * Callbacks can set `passThru` to true to have the CommandClientException
     * be thrown again.
     *
     * @throws Exception
     */
    protected function doHandle()
    {
        //What code are we dealing with?
        $code = $this->getResponseCode();

        //Do we even have a callback for this code?
        if (array_key_exists($code, $this->codeCallables)) {
            $callable = $this->codeCallables[$code];
            //Call our callback with the exception
            $response = $callable($this->getException());

            if ($response instanceof ResponseInterface) {
                $this->setResult($response);
            }
        }

        return null;
    }
}
