<?php

namespace Cob\Bundle\ApiServicesBundle\Models;

use Cob\Bundle\ApiServicesBundle\Exceptions\ResponseModelCollectionException;

class ResponseModelCollectionConfig
{
    use ResponseModelConfigSharedTrait;

    private $countCommand;

    private $countArgs;

    /**
     * @var string
     */
    private $collectionPath;

    private $loadMaxResults = 150;

    /**
     * @var string
     */
    private $childResponseModelClass;

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
     * @return string
     */
    public function getCountCommand(): string
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
}