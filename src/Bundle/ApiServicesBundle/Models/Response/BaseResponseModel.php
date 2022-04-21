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
use Cob\Bundle\ApiServicesBundle\Models\Util\LogUtil;
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
        static::logLoad('loadAsync', $loadConfig);
        return AsyncLoader::load(static::getConfig(), $loadConfig);
    }

    public static function load(LoadConfig $loadConfig): ResponseModel {
        static::logLoad('load', $loadConfig);
        return Loader::load(static::getConfig(), $loadConfig);
    }

    public static function withData(LoadConfig $loadConfig): ResponseModel {
        static::logLoad('withData', $loadConfig);
        return WithDataLoader::load(static::getConfig(), $loadConfig);
    }

    public static function withRawData(LoadConfig $loadConfig): ResponseModel {
        static::logLoad('withRawData', $loadConfig);
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

    /**
     * Log load configuration details.
     *
     * This is helpful for debugging.
     *
     * @param string     $strategy   a string representing the strategy being used to load data
     * @param LoadConfig $loadConfig the load configuration to log details about.
     */
    protected static function logLoad(string $strategy, LoadConfig $loadConfig) {
        LogUtil::lazyDebug(
            $loadConfig->getClient()->getOutput(),
            function () use ($loadConfig, $strategy) {
                return static::getConfig() . PHP_EOL
                    . sprintf('Loading %s using "%s" strategy...', static::class, $strategy) . PHP_EOL
                    . $loadConfig;
            }
        );
    }
}
