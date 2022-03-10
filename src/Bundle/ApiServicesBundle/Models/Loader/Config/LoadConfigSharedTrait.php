<?php

namespace Cob\Bundle\ApiServicesBundle\Models\Loader\Config;

use Cob\Bundle\ApiServicesBundle\Exceptions\LoadConfigRequiredPropertyException;
use Cob\Bundle\ApiServicesBundle\Exceptions\ResponseModelException;
use Cob\Bundle\ApiServicesBundle\Models\ExceptionHandlers\ExceptionHandlerInterface;
use Cob\Bundle\ApiServicesBundle\Models\ExceptionHandlers\ResponseModelExceptionHandler;
use Cob\Bundle\ApiServicesBundle\Models\ResponseModel;
use Cob\Bundle\ApiServicesBundle\Models\ResponseModelCollection;
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

    /**
     * @return ExceptionHandlerInterface
     */
    public function getHandler(): ExceptionHandlerInterface
    {
        return $this->handler ??  ResponseModelExceptionHandler::passThruAndWrapWith(
                ResponseModelException::class,
                ['An exception was thrown during loading']
            );
    }

    /**
     * @return ServiceClientInterface
     */
    public function getClient(): ServiceClientInterface
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
}