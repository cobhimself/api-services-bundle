<?php

namespace Cob\Bundle\ApiServicesBundle\Models\Config;

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
}
