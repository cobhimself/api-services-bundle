<?php

namespace Cob\Bundle\ApiServicesBundle\Models\Loader\Config;

use Cob\Bundle\ApiServicesBundle\Exceptions\LoadConfigRequiredPropertyException;
use Cob\Bundle\ApiServicesBundle\Exceptions\ResponseModelException;
use Cob\Bundle\ApiServicesBundle\Models\ExceptionHandlers\ExceptionHandlerInterface;
use Cob\Bundle\ApiServicesBundle\Models\ExceptionHandlers\ResponseModelExceptionHandler;
use Cob\Bundle\ApiServicesBundle\Models\Response\Collection\ResponseModelCollection;
use Cob\Bundle\ApiServicesBundle\Models\Response\ResponseModel;
use Cob\Bundle\ApiServicesBundle\Models\ServiceClient;
use Cob\Bundle\ApiServicesBundle\Models\ServiceClientInterface;

trait LoadConfigSharedTrait
{
    /**
     * @var array
     */
    private $commandArgs = [];

    /**
     * @var ResponseModel|ResponseModelCollection
     */
    private $parent;

    /**
     * @var bool
     */
    private $clearCache = false;

    /**
     * @var ServiceClientInterface
     */
    private $client;

    /**
     * @var ExceptionHandlerInterface
     */
    private $handler;

    /**
     * @var array
     */
    private $existingData;

    /**
     * @var mixed
     */
    private $rawData;

    /**
     * @return array
     */
    public function getCommandArgs(): array
    {
        return $this->commandArgs;
    }

    /**
     * @return ResponseModel|ResponseModelCollection
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @return bool
     */
    public function doClearCache(): bool
    {
        return $this->clearCache;
    }

    public function hasExceptionHandler(): bool
    {
        return !is_null($this->handler);
    }

    /**
     * Setup a sane default exception handler for use with our loading method.
     *
     * We're going to pass through any exception by default. This means any
     * connection issues which we might be ok with swallowing will be passed
     * through. @see ClientCommandExceptionHandler for ways to handle specific
     * HTTP error codes.
     *
     * @return ExceptionHandlerInterface
     */
    public function getExceptionHandler(): ExceptionHandlerInterface
    {
        return $this->handler ??  ResponseModelExceptionHandler::passThruAndWrapWith(
                ResponseModelException::class,
                ['An exception was thrown during loading:']
            );
    }

    /**
     * @return ServiceClient
     */
    public function getClient(): ServiceClient
    {
        return $this->client;
    }

    /**
     * @return array
     */
    public function getExistingData(): array
    {
        $this->confirmNotNull('existingData', $this->existingData);

        return $this->existingData;
    }

    private function confirmNotNull(string $property, $value)
    {
        if (is_null($value)) {
            throw new LoadConfigRequiredPropertyException($property);
        }
    }

    /**
     * @return mixed
     */
    public function getRawData() {
        $this->confirmNotNull('rawData', $this->rawData);

        return $this->rawData;
    }
}
