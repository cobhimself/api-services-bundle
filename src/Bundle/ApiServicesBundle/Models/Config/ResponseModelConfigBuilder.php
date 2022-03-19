<?php

namespace Cob\Bundle\ApiServicesBundle\Models\Config;

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
        array_map(function (callable $callable) {
            if(!is_callable($callable)) {
                throw new \InvalidArgumentException("The provided callback array MUST contain callable items!");
            }

            $this->addInitCallback($callable);
        }, $callbacks);

        return $this;
    }

    public function holdsRawData(bool $holdsRawData = true): ResponseModelConfigBuilder
    {
        $this->holdsRawData = $holdsRawData;

        return $this;
    }

    public function build(): ResponseModelConfig
    {
        return new ResponseModelConfig(
            $this->responseModelClass,
            $this->command ?? '',
            $this->defaultArgs ?? [],
            $this->holdsRawData,
            $this->initCallbacks
        );
    }

    public function extend(ResponseModelConfig $config): ResponseModelConfigBuilder
    {
        return (new ResponseModelConfigBuilder())
            ->responseModelClass($config->getResponseModelClass())
            ->command($config->getCommand())
            ->defaultArgs($config->getDefaultArgs())
            ->holdsRawData($config->holdsRawData())
            ->initCallbacks($config->getInitCallbacks());
    }
}
