<?php

namespace Cob\Bundle\ApiServicesBundle\Models;

use Cob\Bundle\ApiServicesBundle\Exceptions\ResponseModelException;
use Cob\Bundle\ApiServicesBundle\Models\Loader\State\LoadState;
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
             * @var ResponseModelConfig $config
             */
            $config = static::getResponseModelConfig();
            $response = $this->loadPromise->wait();

            if ($config->holdsRawData()) {
                $this->data->setRawData($response);
            } else {
                $this->data->setData($response);
            }

            $config->doInits($this);

            $this->loadState = LoadState::loaded();
        }
    }

    public function getRawData()
    {
        if (!static::getResponseModelConfig()->holdsRawData()) {
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
        if (static::getResponseModelConfig()->holdsRawData()) {
            throw new ResponseModelException(sprintf(
                $msgTemplate,
                static::class
            ));
        }
    }
}