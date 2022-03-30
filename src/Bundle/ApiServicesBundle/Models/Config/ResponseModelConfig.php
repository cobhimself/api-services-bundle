<?php

namespace Cob\Bundle\ApiServicesBundle\Models\Config;

use Cob\Bundle\ApiServicesBundle\Models\ExceptionHandlers\ExceptionHandlerInterface;
use Cob\Bundle\ApiServicesBundle\Models\Response\ResponseModel;

class ResponseModelConfig
{
    use ResponseModelConfigSharedTrait;

    /**
     * @var bool whether or not the response model this config is associated with holds raw data or if it is structured.
     */
    private $holdsRawData;

    public function __construct(
        string $responseModelClass,
        string $command = null,
        array $defaultArgs = null,
        bool $holdsRawData = null,
        array $initCallbacks = null,
        ExceptionHandlerInterface $defaultExceptionHandler = null
    ) {
        $this->responseModelClass      = $responseModelClass;
        $this->command                 = $command ?? '';
        $this->defaultArgs             = $defaultArgs ?? [];
        $this->holdsRawData            = $holdsRawData ?? false;
        $this->initCallbacks           = $initCallbacks ?? [];
        $this->defaultExceptionHandler = $defaultExceptionHandler;
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
