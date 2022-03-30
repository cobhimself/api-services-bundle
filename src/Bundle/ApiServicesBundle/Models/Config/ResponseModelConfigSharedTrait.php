<?php

namespace Cob\Bundle\ApiServicesBundle\Models\Config;

use Cob\Bundle\ApiServicesBundle\Exceptions\ResponseModelException;
use Cob\Bundle\ApiServicesBundle\Models\ExceptionHandlers\ExceptionHandlerInterface;
use Cob\Bundle\ApiServicesBundle\Models\ExceptionHandlers\ResponseModelExceptionHandler;

trait ResponseModelConfigSharedTrait
{
    /**
     * @var string
     */
    private $command;

    /**
     * @var array
     */
    private $defaultArgs = [];

    /**
     * @var array an array of callbacks to be called upon initialization of this model after loading.
     */
    private $initCallbacks = [];

    /**
     * @var string the FQCN of the response model this config belongs to
     */
    private $responseModelClass;

    /**
     * @var ExceptionHandlerInterface
     */
    private $defaultExceptionHandler;

    /**
     * @return string
     */
    public function getCommand(): string
    {
        return $this->command;
    }

    /**
     * @return array
     */
    public function getDefaultArgs(): array
    {
        return $this->defaultArgs;
    }

    /**
     * @return string
     */
    public function getResponseModelClass(): string
    {
        return $this->responseModelClass;
    }

    /**
     * @return array
     */
    public function getInitCallbacks(): array {
        return $this->initCallbacks;
    }

    public function getDefaultExceptionHandler(): ExceptionHandlerInterface {
        return $this->defaultExceptionHandler ??  ResponseModelExceptionHandler::passThruAndWrapWith(
                ResponseModelException::class,
                ['An exception was thrown during loading:']
            );
    }
}
