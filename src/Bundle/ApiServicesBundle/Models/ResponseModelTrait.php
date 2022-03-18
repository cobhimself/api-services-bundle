<?php

namespace Cob\Bundle\ApiServicesBundle\Models;

use Cob\Bundle\ApiServicesBundle\Exceptions\ResponseModelException;
use Cob\Bundle\ApiServicesBundle\Models\ExceptionHandlers\ExceptionHandlerInterface;
use Cob\Bundle\ApiServicesBundle\Models\ExceptionHandlers\ResponseModelExceptionHandler;
use Cob\Bundle\ApiServicesBundle\Models\Loader\LoadState;
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
     * Setup a sane default exception handler for use with our loading method.
     *
     * We're going to pass through any exception by default. This means any
     * connection issues which we might be ok with swallowing will be passed
     * through. @see ClientCommandExceptionHandler for ways to handle specific
     * HTTP error codes.
     *
     * @return ExceptionHandlerInterface
     */
    protected function getDefaultExceptionHandler(): ExceptionHandlerInterface
    {
        return ResponseModelExceptionHandler::passThruAndWrapWith(
            ResponseModelException::class,
            [sprintf('Could not load response model %s', static::class)]
        );
    }
}
