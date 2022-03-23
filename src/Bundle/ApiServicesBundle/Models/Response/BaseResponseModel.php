<?php

namespace Cob\Bundle\ApiServicesBundle\Models\Response;

use Cob\Bundle\ApiServicesBundle\Models\Config\ResponseModelConfig;
use Cob\Bundle\ApiServicesBundle\Models\Config\ResponseModelConfigBuilder;
use Cob\Bundle\ApiServicesBundle\Models\DotData;
use Cob\Bundle\ApiServicesBundle\Models\Loader\AsyncLoader;
use Cob\Bundle\ApiServicesBundle\Models\Loader\Config\LoadConfig;
use Cob\Bundle\ApiServicesBundle\Models\Loader\Config\LoadConfigBuilder;
use Cob\Bundle\ApiServicesBundle\Models\Loader\Loader;
use Cob\Bundle\ApiServicesBundle\Models\Loader\LoadState;
use Cob\Bundle\ApiServicesBundle\Models\Loader\WithDataLoader;
use Cob\Bundle\ApiServicesBundle\Models\Loader\WithRawDataLoader;
use Cob\Bundle\ApiServicesBundle\Models\ServiceClientInterface;
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
     * @param null $parent
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

        //We can go ahead and set the data for the model if it has already been loaded. Otherwise we wait until
        //the first time we attempt to get data.
        if ($desiredLoadState->isLoaded() || $desiredLoadState->isLoadedWithData()) {
            $this->data = DotData::of($this->loadPromise->wait());
            $config->doInits($this);
        }

        $this->loadState = $desiredLoadState;
    }

    protected static function setup(): ResponseModelConfigBuilder
    {
        return new ResponseModelConfigBuilder();
    }

    public static function getConfig(): ResponseModelConfig
    {
        static $config;

        //We only want to establish our $config once.
        if(is_null($config)) {
            $builder = static::setup();
            $builder->responseModelClass(static::class);
            $config = $builder->build();
        }

        return $config;
    }

    public static function loadAsync(LoadConfig $loadConfig): ResponseModel {
        return AsyncLoader::load(static::getConfig(), $loadConfig);
    }

    public static function load(LoadConfig $loadConfig): ResponseModel {
        return Loader::load(static::getConfig(), $loadConfig);
    }

    public static function withData(LoadConfig $loadConfig): ResponseModel {
        return WithDataLoader::load(static::getConfig(), $loadConfig);
    }

    public static function withRawData(LoadConfig $loadConfig): ResponseModel {
        return WithRawDataLoader::load(static::getConfig(), $loadConfig);
    }

    public static function using(ServiceClientInterface $client): LoadConfigBuilder
    {
        return LoadConfig::builder(static::class, $client);
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