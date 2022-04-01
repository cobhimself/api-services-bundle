<?php

namespace Cob\Bundle\ApiServicesBundle\Models\Response;

use Cob\Bundle\ApiServicesBundle\Exceptions\IncorrectParentResponseModel;
use Cob\Bundle\ApiServicesBundle\Exceptions\ResponseModelException;
use Cob\Bundle\ApiServicesBundle\Exceptions\ResponseModelSetupException;
use Cob\Bundle\ApiServicesBundle\Models\Config\ResponseModelCollectionConfig;
use Cob\Bundle\ApiServicesBundle\Models\Config\ResponseModelConfig;
use Cob\Bundle\ApiServicesBundle\Models\DotData;
use Cob\Bundle\ApiServicesBundle\Models\ExceptionHandlers\ExceptionHandlerInterface;
use Cob\Bundle\ApiServicesBundle\Models\ExceptionHandlers\ResponseModelExceptionHandler;
use Cob\Bundle\ApiServicesBundle\Models\Loader\LoadState;
use Cob\Bundle\ApiServicesBundle\Models\ServiceClientInterface;
use Cob\Bundle\ApiServicesBundle\Models\Util\ClassUtil;
use GuzzleHttp\Promise\PromiseInterface;

/**
 * Trait shared among both individual response models and response model collections.
 */
trait ResponseModelTrait
{
    /**
     * @var DotData the data for this model
     */
    private $data;

    /**
     * @var PromiseInterface the Promise used to load the data within the model
     */
    private $loadPromise;

    /**
     * @var LoadState the current load state of the model
     */
    private $loadState;

    /**
     * @var ServiceClientInterface the service client we are using to run service commands
     */
    private $client;

    public function dot(string $key, $default = false)
    {
        $this->confirmNoRawData("%s holds raw data so the dot method cannot be used. Use getRawData instead.");

        return $this->getData()->dot($key, $default);
    }

    /**
     * Finalize the data for this model after it's been loaded.
     */
    protected function finalizeData()
    {
        //This method is empty because, by default, we do not perform additional finalization on our data
    }

    public function toArray(): array
    {
        $this->confirmNoRawData("%s holds raw data so there is no array representation. Use getRawData instead.");

        return $this->getData()->toArray();
    }

    protected function confirmLoaded()
    {
        if (is_null($this->data)) {
            $this->data = new DotData();
        }

        if ($this->loadState->isWaiting()) {
            /**
             * @var ResponseModelConfig|ResponseModelCollectionConfig $config
             */
            $config = static::getConfig();
            $response = $this->loadPromise->wait();

            if (
                //We only worry about raw data with response models, not collections
                ClassUtil::isValidResponseModel(static::class)
                && $config->holdsRawData()
            ) {
                $this->data->setRawData($response);
            } else {
                $this->data->setData($response);
            }

            $this->loadState = LoadState::loaded();

            $this->finalizeData();
            $config->doInits($this);
        }
    }

    public function getRawData()
    {
        if (!static::getConfig()->holdsRawData()) {
            throw new ResponseModelException(sprintf(
                "%s holds structured data; use the dot method instead!",
                static::class
            ));
        }

        return $this->getData()->getRawData();
    }

    protected function getData(): DotData
    {
        $this->confirmLoaded();

        return $this->data;
    }

    private function confirmNoRawData(string $msgTemplate)
    {
        //Collections cannot contain raw data
        if (ClassUtil::isValidResponseModelCollection(static::class)) {
            return;
        }

        if (static::getConfig()->holdsRawData()) {
            throw new ResponseModelException(sprintf(
                $msgTemplate,
                static::class
            ));
        }
    }

    public function isLoaded(): bool
    {
        return $this->loadState->isLoaded();
    }

    public function isLoadedWithData(): bool
    {
        return $this->loadState->isLoadedWithData();
    }

    public function isWaiting(): bool
    {
        return $this->loadState->isWaiting();
    }

    /**
     * @return ServiceClientInterface
     */
    public function getClient(): ServiceClientInterface
    {
        return $this->client;
    }

    /**
     * Confirm the given property exists in the response model.
     *
     * This method helps us make sure our models are setup correctly and
     * fails early if they aren't.
     *
     * @param string $property the property to check
     *
     * @throws ResponseModelSetupException
     */
    protected function checkForPropertyException(string $property)
    {
        if (!property_exists($this, $property) || null === $this->$property) {
            throw new ResponseModelSetupException(sprintf(
                'Could not get property \'%s\'!' . PHP_EOL . "\tIN: %s",
                $property,
                get_class($this)
            ));
        }
    }

    /**
     * Confirm the model we are attempting to set as the parent model is of the
     * correct instance.
     *
     * @param mixed $parent the parent class which the model MUST be an instance of
     * @param mixed $actual the instance to confirm
     *
     * @throws IncorrectParentResponseModel if the parent class is not the same as the given actual class.
     */
    public static function confirmCorrectParentModel($parent, $actual)
    {
        ClassUtil::confirmCorrectParentModel($parent, $actual, static::class);
    }
}
