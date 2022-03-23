<?php

namespace Cob\Bundle\ApiServicesBundle\Models\Config;

use Cob\Bundle\ApiServicesBundle\Exceptions\ResponseModelSetupException;
use Cob\Bundle\ApiServicesBundle\Models\Response\Collection\ResponseModelCollection;
use Cob\Bundle\ApiServicesBundle\Models\Util\ClassUtil;

class ResponseModelCollectionConfig
{
    use ResponseModelConfigSharedTrait;

    const CHUNK_COMMAND_MAX_RESULTS_DEFAULT = 25;

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
        string $command = '',
        array $defaultArgs = [],
        string $collectionPath = '',
        string $countCommand = null,
        array $countArgs = [],
        string $countValuePath = '',
        int $loadMaxResults = 150,
        callable $buildCountArgsCallback = null,
        int $chunkCommandMaxResults = self::CHUNK_COMMAND_MAX_RESULTS_DEFAULT,
        array $initCallbacks = []
    ) {
        $this->responseModelClass = $responseModelClass;
        $this->command = $command;
        $this->defaultArgs = $defaultArgs;
        $this->collectionPath = $collectionPath;

        ClassUtil::confirmValidResponseModel($childResponseModelClass);

        $this->childResponseModelClass = $childResponseModelClass;

        if (!is_null($countCommand)) {
            $this->countCommand = $countCommand;
        }

        $this->countArgs = $countArgs;
        $this->countValuePath = $countValuePath;
        $this->loadMaxResults = $loadMaxResults;

        if (!is_null($buildCountArgsCallback)) {
            $this->buildCountArgsCallback = $buildCountArgsCallback;
        }

        $this->chunkCommandMaxResults = $chunkCommandMaxResults;
        $this->initCallbacks = $initCallbacks;
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

    public function getBuildCountArgsCallback(): callable {
        if (is_null($this->buildCountArgsCallback)) {
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
}
