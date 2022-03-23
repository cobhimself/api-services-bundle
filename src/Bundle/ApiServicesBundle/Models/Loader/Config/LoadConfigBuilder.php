<?php

namespace Cob\Bundle\ApiServicesBundle\Models\Loader\Config;

use Cob\Bundle\ApiServicesBundle\Exceptions\ResponseModelException;
use Cob\Bundle\ApiServicesBundle\Models\ExceptionHandlers\ExceptionHandlerInterface;
use Cob\Bundle\ApiServicesBundle\Models\ResponseModel;
use Cob\Bundle\ApiServicesBundle\Models\ResponseModelCollection;
use Cob\Bundle\ApiServicesBundle\Models\ServiceClientInterface;
use Cob\Bundle\ApiServicesBundle\Models\Util\ClassUtil;

class LoadConfigBuilder
{
    use LoadConfigBuilderSharedTrait;

    /**
     * @var mixed
     */
    private $rawData;

    public function __construct(string $modelClass, ServiceClientInterface $client) {
        $this->validateModelClass($modelClass);

        $this->modelClass = $modelClass;
        $this->client = $client;
    }

    public function clearCache(bool $clear = true): LoadConfigBuilder
    {
        $this->clearCache = $clear;

        return $this;
    }

    public function withParent($parent): LoadConfigBuilder
    {
        ClassUtil::confirmValidResponseModelOrCollection($parent);

        $this->parent = $parent;

        return $this;
    }

    public function handleExceptionsWith(ExceptionHandlerInterface $handler): LoadConfigBuilder
    {
        $this->handler = $handler;

        return $this;
    }

    public function build(): LoadConfig
    {
        return new LoadConfig(
            $this->client,
            $this->commandArgs,
            $this->parent,
            $this->clearCache,
            $this->handler,
            $this->existingData,
            $this->rawData
        );
    }

    public function withCommandArgs(array $commandArgs): LoadConfigBuilder
    {
        $this->commandArgs = $commandArgs;

        return $this;
    }

    public function existingData(array $existingData): LoadConfigBuilder
    {
        $this->existingData = $existingData;

        return $this;
    }

    public function rawData($rawData): LoadConfigBuilder
    {
        $this->rawData = $rawData;

        return $this;
    }

    protected function validateModelClass(string $modelClass)
    {
        ClassUtil::confirmValidResponseModel($modelClass);
    }

    public function load(): ResponseModel
    {
        return $this->provide('load');
    }

    public function loadAsync(): ResponseModel
    {
        return $this->provide('loadAsync');
    }

    public function withData(array $existingData): ResponseModel
    {
        $this->existingData = $existingData;

        return $this->provide('withData');
    }

    /**
     * @param ResponseModel|ResponseModelCollection $parent
     * @param string                                $dotPath
     *
     * @return ResponseModel
     */
    public function withDataFromParent($parent, string $dotPath): ResponseModel
    {
        if (!$parent->dot($dotPath)) {
            throw new ResponseModelException(sprintf(
                "Could not load data from '%s' at path '%s'.",
                get_class($parent),
                $dotPath
            ));
        }

        return $this->withParent($parent)->withData($parent->dot($dotPath));
    }

    public function withRawData($rawData): ResponseModel
    {
        $this->rawData = $rawData;

        return $this->provide('withRawData');
    }

    private function provide(string $loadMethod): ResponseModel
    {
        return call_user_func([$this->modelClass, $loadMethod], $this->build());
    }
}
