<?php

namespace Cob\Bundle\ApiServicesBundle\Models\Loader\Config;

use Cob\Bundle\ApiServicesBundle\Models\ExceptionHandlers\ExceptionHandlerInterface;
use Cob\Bundle\ApiServicesBundle\Models\ResponseModelCollection;
use Cob\Bundle\ApiServicesBundle\Models\ServiceClientInterface;
use Cob\Bundle\ApiServicesBundle\Models\Util\ClassUtil;

class CollectionLoadConfigBuilder
{
    use LoadConfigBuilderSharedTrait;

    /**
     * @var array
     */
    private $countCommandArgs = [];

    public function __construct(string $modelClass, ServiceClientInterface $client) {
        $this->validateModelClass($modelClass);

        $this->modelClass = $modelClass;
        $this->client = $client;
    }

    public function clearCache(bool $clear = true): CollectionLoadConfigBuilder
    {
        $this->clearCache = $clear;

        return $this;
    }

    public function withParent($parent): CollectionLoadConfigBuilder
    {
        ClassUtil::confirmValidResponseModelOrCollection($parent);

        $this->parent = $parent;

        return $this;
    }

    public function handleExceptionsWith(ExceptionHandlerInterface $handler): CollectionLoadConfigBuilder
    {
        $this->handler = $handler;

        return $this;
    }

    public function withCommandArgs(array $commandArgs): CollectionLoadConfigBuilder
    {
        $this->commandArgs = $commandArgs;

        return $this;
    }


    /**
     * @param array $countCommandArgs
     * @return CollectionLoadConfigBuilder
     */
    public function withCountCommandArgs(array $countCommandArgs): CollectionLoadConfigBuilder
    {
        $this->countCommandArgs = $countCommandArgs;

        return $this;
    }

    public function validateModelClass(string $modelClass)
    {
        ClassUtil::confirmValidResponseModelCollection($modelClass);
    }

    public function build(): CollectionLoadConfig
    {
        return new CollectionLoadConfig(
            $this->client,
            $this->commandArgs,
            $this->countCommandArgs,
            $this->parent,
            $this->clearCache,
            $this->handler,
            $this->existingData
        );
    }

    public function load(): ResponseModelCollection
    {
        return $this->provide('load');
    }

    public function loadAsync(): ResponseModelCollection
    {
        return $this->provide('loadAsync');
    }

    public function withData(array $existingData): ResponseModelCollection
    {
        $this->existingData = $existingData;

        return $this->provide('withData');
    }

    private function provide(string $loadMethod): ResponseModelCollection
    {
        return call_user_func([$this->modelClass, $loadMethod], $this->build());
    }
}