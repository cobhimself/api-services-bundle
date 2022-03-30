<?php

namespace Cob\Bundle\ApiServicesBundle\Models\Config;

use Cob\Bundle\ApiServicesBundle\Exceptions\ResponseModelSetupException;
use Cob\Bundle\ApiServicesBundle\Models\ExceptionHandlers\ExceptionHandlerInterface;
use InvalidArgumentException;
use TypeError;

class ResponseModelConfigBuilder {
    use ResponseModelConfigSharedTrait;

    private $holdsRawData = false;

    /**
     * @param string $responseModelClass
     *
     * @return ResponseModelConfigBuilder
     */
    public function responseModelClass(string $responseModelClass): ResponseModelConfigBuilder
    {
        $this->responseModelClass = $responseModelClass;

        return $this;
    }

    public function command(string $command): ResponseModelConfigBuilder
    {
        $this->command = $command;

        return $this;
    }

    public function defaultArgs(array $defaultArgs): ResponseModelConfigBuilder
    {
        $this->defaultArgs = $defaultArgs;

        return $this;
    }

    public function addInitCallback(callable $initCallback): ResponseModelConfigBuilder
    {
        $this->initCallbacks[] = $initCallback;

        return $this;
    }

    public function initCallbacks(array $callbacks): ResponseModelConfigBuilder
    {
        try {
            array_map(function (callable $callable) {
                $this->addInitCallback($callable);
            }, $callbacks);
        } catch (TypeError $e) {
            throw new InvalidArgumentException("The provided callback array MUST contain callable items!");
        }

        return $this;
    }

    public function holdsRawData(bool $holdsRawData = true): ResponseModelConfigBuilder
    {
        $this->holdsRawData = $holdsRawData;

        return $this;
    }

    /**
     * Specify the default exception handler the response model should use when loading data.
     *
     * @param ExceptionHandlerInterface $handler the handler to use by default
     *
     * @return $this
     */
    public function defaultExceptionHandler(ExceptionHandlerInterface $handler): ResponseModelConfigBuilder
    {
        $this->defaultExceptionHandler = $handler;

        return $this;
    }

    protected function validate()
    {
        ResponseModelSetupException::confirmResponseModelClassSet(
            $this->responseModelClass
        );
    }

    public function build(): ResponseModelConfig
    {
        $this->validate();

        return new ResponseModelConfig(
            $this->responseModelClass,
            $this->command ?? '',
            $this->defaultArgs ?? [],
            $this->holdsRawData,
            $this->initCallbacks,
            $this->defaultExceptionHandler
        );
    }

    public static function extend(ResponseModelConfig $config): ResponseModelConfigBuilder
    {
        return (new ResponseModelConfigBuilder())
            ->responseModelClass($config->getResponseModelClass())
            ->command($config->getCommand())
            ->defaultArgs($config->getDefaultArgs())
            ->holdsRawData($config->holdsRawData())
            ->initCallbacks($config->getInitCallbacks())
            ->defaultExceptionHandler($config->getDefaultExceptionHandler());
    }
}
