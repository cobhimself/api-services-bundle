<?php

namespace Cob\Bundle\ApiServicesBundle\Models;

trait ResponseModelConfigSharedTrait
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
     * @var string the FQCN of the response model this config belongs to
     */
    private $responseModelClass;

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
}