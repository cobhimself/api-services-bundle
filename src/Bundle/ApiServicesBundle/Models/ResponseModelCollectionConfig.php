<?php

namespace Cob\Bundle\ApiServicesBundle\Models;

use Cob\Bundle\ApiServicesBundle\Exceptions\ResponseModelCollectionException;
use Cob\Bundle\ApiServicesBundle\Exceptions\ResponseModelSetupException;

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

    public function __construct(
        string $command,
        array $defaultArgs,
        string $collectionPath,
        string $countCommand = null,
        array $countArgs = []
    ) {
        $this->command = $command;
        $this->defaultArgs = $defaultArgs;
        $this->collectionPath = $collectionPath;

        if (!is_null($countCommand)) {
            $this->countCommand = $countCommand;
        }

        $this->countArgs = $countArgs;
    }

    public static function none(): ResponseModelCollectionConfig
    {
        return new ResponseModelCollectionConfig('', [], '');
    }

    public function setChildResponseModelClass(string $childClass) {
        $this->childResponseModelClass = $childClass;
    }

    public function getChildResponseModelClass(): string
    {
        if (is_null($this->childResponseModelClass)) {
            throw new ResponseModelCollectionException(sprintf(
                "The ResponseModelCollectionConfig for %s MUST have a childResponseModelClass defined!",
                $this->getResponseModelClass()
            ));
        }

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

    /**
     * @param int $loadMaxResults
     */
    public function setLoadMaxResults(int $loadMaxResults)
    {
        $this->loadMaxResults = $loadMaxResults;
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

    public function setBuildCountArgsCallback(callable $callback)
    {
        $this->buildCountArgsCallback = $callback;
    }

    /**
     * @return string
     */
    public function getCountValuePath(): string
    {
        return $this->countValuePath;
    }

    /**
     * @param string $countValuePath
     */
    public function setCountValuePath(string $countValuePath)
    {
        $this->countValuePath = $countValuePath;
    }

    public function getBuildCountArgsCallback()
    {
        if (is_null($this->buildCountArgsCallback)) {
            throw new ResponseModelSetupException(
                'Cannot obtain the buildCountArgsCallback for ' . $this->getResponseModelClass()
            );
        }

        return $this->buildCountArgsCallback;
    }

    public function setChunkCommandMaxResults(int $max)
    {
        $this->chunkCommandMaxResults = $max;
    }

    public function getChunkCommandMaxResults(): int
    {
        return $this->chunkCommandMaxResults ?? self::CHUNK_COMMAND_MAX_RESULTS_DEFAULT;
    }
}