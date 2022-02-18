<?php

namespace Cob\Bundle\ApiServicesBundle\Models;

class ResponseModelConfig
{
    /**
     * @var string
     */
    private $command;

    /**
     * @var array
     */
    private $defaultArgs;

    /**
     * @var array an array of callbacks to be called upon initialization of this model after loading.
     */
    private $initCallbacks = [];

    /**
     * @var bool whether or not the response model this config is associated with holds raw data or if it is structured.
     */
    private $holdsRawData = false;

    /**
     * @var string the FQCN of the response model this config belongs to
     */
    private $responseModelClass;

    public function __construct(string $command, array $defaultArgs)
    {
        $this->command = $command;
        $this->defaultArgs = $defaultArgs;
    }

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
     * @param string $responseModelClass
     */
    public function setResponseModelClass(string $responseModelClass)
    {
        $this->responseModelClass = $responseModelClass;
    }

    /**
     * @return string
     */
    public function getResponseModelClass(): string
    {
        return $this->responseModelClass;
    }

    public function addInitCallback(callable $initCallback)
    {
        $this->initCallbacks[] = $initCallback;
    }

    public function doInits(ResponseModel $model)
    {
        foreach ($this->initCallbacks as $callback) {
            $callback($model);
        }
    }

    /**
     * @return bool
     */
    public function holdsRawData(): bool
    {
        return $this->holdsRawData;
    }

    /**
     * @param bool $holdsRawData
     */
    public function setHoldsRawData(bool $holdsRawData)
    {
        $this->holdsRawData = $holdsRawData;
    }
}