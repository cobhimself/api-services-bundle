<?php

namespace Cob\Bundle\ApiServicesBundle\Models\Config;

use Cob\Bundle\ApiServicesBundle\Models\ResponseModel;

class ResponseModelConfig
{
    use ResponseModelConfigSharedTrait;

    /**
     * @var bool whether or not the response model this config is associated with holds raw data or if it is structured.
     */
    private $holdsRawData = false;

    public function __construct(string $command, array $defaultArgs)
    {
        $this->command = $command;
        $this->defaultArgs = $defaultArgs;
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
