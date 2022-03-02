<?php

namespace Cob\Bundle\ApiServicesBundle\Models;

use Cob\Bundle\ApiServicesBundle\Exceptions\ResponseModelSetupException;
use Cob\Bundle\ApiServicesBundle\Models\Loader\AsyncLoader;
use Cob\Bundle\ApiServicesBundle\Models\Loader\Loader;
use Cob\Bundle\ApiServicesBundle\Models\Loader\State\LoadState;
use Cob\Bundle\ApiServicesBundle\Models\Loader\WithDataLoader;
use GuzzleHttp\Promise\PromiseInterface;

/**
 * Base response model all response models should extend from.
 */
class BaseResponseModel implements ResponseModel
{
    use ResponseModelTrait;
    use HasParentTrait;

    /**
     * Establish a new response model with a specific load state and load promise.
     *
     * You are encouraged to utilize the static methods for construction of the response model!
     *
     * @param ServiceClientInterface $client
     * @param LoadState $desiredLoadState
     * @param PromiseInterface $loadPromise
     */
    public function __construct(
        ServiceClientInterface $client,
        LoadState $desiredLoadState,
        PromiseInterface $loadPromise,
        $parent = null
    ) {
        $this->client = $client;
        $this->loadPromise = $loadPromise;

        if (!is_null($parent)) {
            $this->setParent($parent);
        }

        $config = static::getConfig();
        $config->setServiceClient($client);

        //We can go ahead and set the data for the model if it has already been loaded. Otherwise we wait until
        //the first time we attempt to get data.
        if ($desiredLoadState->isLoaded() || $desiredLoadState->isLoadedWithData()) {
            $this->data = new DotData($this->loadPromise->wait());
            $config->doInits($this);
        }

        $this->loadState = $desiredLoadState;
    }

    protected static function setup(): ResponseModelConfig
    {
        throw new ResponseModelSetupException(static::class . " must override the setup method!");
    }

    public static function getConfig(): ResponseModelConfig
    {
        static $config;

        //We only want to establish our $config once.
        if(is_null($config)) {
            $config = static::setup();
            $config->setResponseModelClass(static::class);
        }

        return $config;
    }

    public static function loadAsync(
        ServiceClientInterface $client,
        array $commandArgs = [],
        $parent = null
    ): ResponseModel {
        return AsyncLoader::load(
            static::getConfig(),
            $client,
            $commandArgs,
            [], //No data yet!
            $parent
        );
    }
    
    public static function load(
        ServiceClientInterface $client,
        array $commandArgs = [],
        $parent = null
    ): ResponseModel {
        return Loader::load(
            static::getConfig(),
            $client,
            $commandArgs,
            [], //No data yet!
            $parent
        );
    }

    public static function withData(
        ServiceClientInterface $client,
        array $data,
        $parent = null
    ): ResponseModel {
        return WithDataLoader::load(
            static::getConfig(),
            $client,
            [], //Don't need to supply command args as we already have the data for the model
            $data,
            $parent
        );
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
}