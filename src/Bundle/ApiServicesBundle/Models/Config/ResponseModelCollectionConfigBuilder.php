<?php

namespace Cob\Bundle\ApiServicesBundle\Models\Config;

class ResponseModelCollectionConfigBuilder {

    use ResponseModelConfigSharedTrait;

    /**
     * @var string
     */
    private $countCommand;

    /**
     * @var array
     */
    private $countArgs = [];

    /**
     * @var string
     */
    private $countValuePath = '';

    /**
     * @var string
     */
    private $collectionPath;

    /**
     * @var int
     */
    private $loadMaxResults = 150;

    /**
     * @var string
     */
    private $childResponseModelClass;

    /**
     * @var callable
     */
    private $buildCountArgsCallback;

    /**
     * @var int
     */
    private $chunkCommandMaxResults;

    /**
     * @param string $responseModelClass
     *
     * @return ResponseModelCollectionConfigBuilder
     */
    public function responseModelClass(string $responseModelClass): ResponseModelCollectionConfigBuilder
    {
        $this->responseModelClass = $responseModelClass;

        return $this;
    }


    public function command(string $command): ResponseModelCollectionConfigBuilder
    {
        $this->command = $command;

        return $this;
    }

    public function defaultArgs(array $defaultArgs): ResponseModelCollectionConfigBuilder
    {
        $this->defaultArgs = $defaultArgs;

        return $this;
    }

    public function collectionPath(string $collectionPath): ResponseModelCollectionConfigBuilder
    {
        $this->collectionPath = $collectionPath;

        return $this;
    }

    public function childResponseModelClass(string $childClass): ResponseModelCollectionConfigBuilder
    {
        $this->childResponseModelClass = $childClass;

        return $this;
    }

    public function countCommand(string $countCommand): ResponseModelCollectionConfigBuilder
    {
        $this->countCommand = $countCommand;

        return $this;
    }

    public function countArgs(array $countArgs): ResponseModelCollectionConfigBuilder
    {
        $this->countArgs = $countArgs;

        return $this;
    }

    /**
     * @param string $countValuePath
     *
     * @return ResponseModelCollectionConfigBuilder
     */
    public function countValuePath(string $countValuePath): ResponseModelCollectionConfigBuilder
    {
        $this->countValuePath = $countValuePath;

        return $this;
    }

    /**
     * @param int $loadMaxResults
     *
     * @return ResponseModelCollectionConfigBuilder
     */
    public function loadMaxResults(int $loadMaxResults): ResponseModelCollectionConfigBuilder
    {
        $this->loadMaxResults = $loadMaxResults;

        return $this;
    }

    public function buildCountArgsCallback(callable $callback): ResponseModelCollectionConfigBuilder
    {
        $this->buildCountArgsCallback = $callback;

        return $this;
    }

    public function chunkCommandMaxResults(int $max): ResponseModelCollectionConfigBuilder
    {
        $this->chunkCommandMaxResults = $max;

        return $this;
    }

    public function addInitCallback(callable $callback): ResponseModelCollectionConfigBuilder
    {
        $this->initCallbacks[] = $callback;

        return $this;
    }

    public function initCallbacks(array $callbacks): ResponseModelCollectionConfigBuilder
    {
        array_map(function (callable $callable) {
            if(!is_callable($callable)) {
                throw new \InvalidArgumentException("The provided callback array MUST contain callable items!");
            }

            $this->addInitCallback($callable);
        }, $callbacks);

        return $this;
    }

    public function build(): ResponseModelCollectionConfig
    {
        return new ResponseModelCollectionConfig(
            $this->responseModelClass,
            $this->childResponseModelClass,
            $this->command,
            $this->defaultArgs,
            $this->collectionPath,
            $this->countCommand,
            $this->countArgs,
            $this->countValuePath,
            $this->loadMaxResults,
            $this->buildCountArgsCallback,
            $this->chunkCommandMaxResults
                ?? ResponseModelCollectionConfig::CHUNK_COMMAND_MAX_RESULTS_DEFAULT,
            $this->initCallbacks
        );
    }

    public function extend(ResponseModelCollectionConfig $config): ResponseModelCollectionConfigBuilder
    {
        return (new ResponseModelCollectionConfigBuilder())
            ->responseModelClass($config->getResponseModelClass())
            ->command($config->getCommand())
            ->defaultArgs($config->getDefaultArgs())
            ->collectionPath($config->getCollectionPath())
            ->childResponseModelClass($config->getChildResponseModelClass())
            ->countCommand($config->getCountCommand())
            ->countArgs($config->getCountArgs())
            ->countValuePath($config->getCountValuePath())
            ->loadMaxResults($config->getLoadMaxResults())
            ->buildCountArgsCallback($config->getBuildCountArgsCallback())
            ->chunkCommandMaxResults($config->getChunkCommandMaxResults())
            ->initCallbacks($config->getInitCallbacks());
    }
}
