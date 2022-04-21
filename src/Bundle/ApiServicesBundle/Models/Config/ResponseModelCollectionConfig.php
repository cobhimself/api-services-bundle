<?php

namespace Cob\Bundle\ApiServicesBundle\Models\Config;

use Cob\Bundle\ApiServicesBundle\Exceptions\ResponseModelSetupException;
use Cob\Bundle\ApiServicesBundle\Models\ExceptionHandlers\ExceptionHandlerInterface;
use Cob\Bundle\ApiServicesBundle\Models\Response\Collection\ResponseModelCollection;
use Cob\Bundle\ApiServicesBundle\Models\Util\ClassUtil;
use Cob\Bundle\ApiServicesBundle\Models\Util\LogUtil;

class ResponseModelCollectionConfig
{
    use ResponseModelConfigSharedTrait;

    const CHUNK_COMMAND_MAX_RESULTS_DEFAULT = 25;
    const LOAD_MAX_RESULTS_DEFAULT = 150;

    /**
     * @var string
     */
    private $countCommand;

    /**
     * @var array
     */
    private $countArgs;

    /**
     * @var string
     */
    private $countValuePath;

    /**
     * @var string
     */
    private $collectionPath;

    /**
     * @var int
     */
    private $loadMaxResults;

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

    public function __construct(
        string $responseModelClass,
        string $childResponseModelClass,
        string $command = null,
        array $defaultArgs = null,
        string $collectionPath = null,
        string $countCommand = null,
        array $countArgs = null,
        string $countValuePath = null,
        int $loadMaxResults = null,
        callable $buildCountArgsCallback = null,
        int $chunkCommandMaxResults = null,
        array $initCallbacks = null,
        ExceptionHandlerInterface $defaultExceptionHandler = null
    ) {
        $this->responseModelClass = $responseModelClass;
        $this->childResponseModelClass = $childResponseModelClass;
        ClassUtil::confirmValidResponseModel($childResponseModelClass);

        $this->command                 = $command ?? '';
        $this->defaultArgs             = $defaultArgs ?? [];
        $this->collectionPath          = $collectionPath ?? '';
        $this->countCommand            = $countCommand;
        $this->countArgs               = $countArgs ?? [];
        $this->countValuePath          = $countValuePath ?? '';
        $this->loadMaxResults          = $loadMaxResults ?? self::LOAD_MAX_RESULTS_DEFAULT;
        $this->buildCountArgsCallback  = $buildCountArgsCallback;
        $this->chunkCommandMaxResults  = $chunkCommandMaxResults ?? self::CHUNK_COMMAND_MAX_RESULTS_DEFAULT;
        $this->initCallbacks           = $initCallbacks ?? [];
        $this->defaultExceptionHandler = $defaultExceptionHandler;
    }

    public function getChildResponseModelClass(): string
    {
        return $this->childResponseModelClass;
    }

    public function doInits(ResponseModelCollection $collection)
    {
        foreach ($this->initCallbacks as $callback) {
            $callback($collection);
        }
    }

    /**
     * @return string|null
     */
    public function getCountCommand()
    {
        return $this->countCommand;
    }

    /**
     * @return array
     */
    public function getCountArgs(): array
    {
        return $this->countArgs;
    }

    /**
     * @return int
     */
    public function getLoadMaxResults(): int
    {
        return $this->loadMaxResults;
    }

    public function hasCountCommand(): bool
    {
        return !is_null($this->countCommand);
    }

    /**
     * @return string
     */
    public function getCollectionPath(): string
    {
        return $this->collectionPath;
    }

    /**
     * @return string
     */
    public function getCountValuePath(): string
    {
        return $this->countValuePath;
    }

    public function hasBuildCountArgsCallback(): bool
    {
        return !is_null($this->buildCountArgsCallback);
    }

    public function getBuildCountArgsCallback(): callable {
        if (!$this->hasBuildCountArgsCallback()) {
            throw new ResponseModelSetupException(
                'Cannot obtain the buildCountArgsCallback for ' . $this->getResponseModelClass()
            );
        }

        return $this->buildCountArgsCallback;
    }

    public function getChunkCommandMaxResults(): int
    {
        return $this->chunkCommandMaxResults ?? self::CHUNK_COMMAND_MAX_RESULTS_DEFAULT;
    }

    public static function builder(): ResponseModelCollectionConfigBuilder
    {
        return new ResponseModelCollectionConfigBuilder();
    }

    public function __toString(): string
    {
        return 'Response Model Collection Config:' . PHP_EOL .
            LogUtil::outputStructure([
                'Model'                     => $this->responseModelClass,
                'Child Models'              => $this->childResponseModelClass,
                'Command'                   => $this->command,
                'Default Args'              => json_encode($this->defaultArgs),
                'Collection Path'           => $this->collectionPath,
                'Count Command'             => $this->countCommand ?? '',
                'Count Args'                => json_encode($this->countArgs),
                'Count Value Path'          => $this->countValuePath,
                'Load Max Results'          => $this->loadMaxResults,
                'Build Count Args Callback' => !is_null($this->buildCountArgsCallback),
                'Chunk Command Max Results' => $this->chunkCommandMaxResults,
                'Init Callbacks'            => (sizeof($this->initCallbacks) > 0),
                'Default Exception Handler' => $this->defaultExceptionHandler
                    ? get_class($this->defaultExceptionHandler)
                    : 'false',
            ]);
    }
}
