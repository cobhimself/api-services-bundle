<?php

namespace Cob\Bundle\ApiServicesBundle\Models\Config;

use Cob\Bundle\ApiServicesBundle\Models\Response\ResponseModel;

class ResponseModelConfig
{
    use ResponseModelConfigSharedTrait;

    /**
     * @var bool whether or not the response model this config is associated with holds raw data or if it is structured.
     */
    private $holdsRawData = false;

    public function __construct(
        string $responseModelClass,
        string $command = '',
        array $defaultArgs = [],
        bool $holdsRawData = false,
        array $initCallbacks = []
    ) {
        $this->responseModelClass = $responseModelClass;
        $this->command = $command;
        $this->defaultArgs = $defaultArgs;
        $this->holdsRawData = $holdsRawData;
        $this->initCallbacks = $initCallbacks;
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

    public static function builder(): ResponseModelConfigBuilder
    {
        return new ResponseModelConfigBuilder();
    }
}
