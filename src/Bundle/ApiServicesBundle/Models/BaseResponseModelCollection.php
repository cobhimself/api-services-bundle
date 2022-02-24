<?php

namespace Cob\Bundle\ApiServicesBundle\Models;

use Cob\Bundle\ApiServicesBundle\Exceptions\ResponseModelSetupException;
use Cob\Bundle\ApiServicesBundle\Models\Loader\AsyncCollectionLoader;
use Cob\Bundle\ApiServicesBundle\Models\Loader\CollectionLoader;
use Cob\Bundle\ApiServicesBundle\Models\Loader\State\LoadState;
use Cob\Bundle\ApiServicesBundle\Models\Loader\WithDataCollectionLoader;
use Doctrine\Common\Collections\ArrayCollection;
use GuzzleHttp\Promise\PromiseInterface;

/**
 * Base response model all response models should extend from.
 */
class BaseResponseModelCollection
    extends ArrayCollection
    implements ResponseModelCollection
{
    use ResponseModelTrait;

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
        PromiseInterface $loadPromise
    ) {
        parent::__construct([]);

        $this->client = $client;
        $this->loadPromise = $loadPromise;
        $this->loadState = $desiredLoadState;

        $config = static::getResponseModelCollectionConfig();

        $config->addInitCallback(function () use ($config) {
            $data = $this->getData()->dot($config->getCollectionPath());
            foreach ($this->getData()->dot($config->getCollectionPath()) as $child) {
                $this->addResponse($this->client, $child);
            }
        });

        //We can go ahead and set the data for the model if it has already been loaded. Otherwise we wait until
        //the first time we attempt to get data.
        if ($desiredLoadState->isLoaded() || $desiredLoadState->isLoadedWithData()) {
            $this->data = new DotData($this->loadPromise->wait());
            static::getResponseModelCollectionConfig()->doInits($this);
        }
    }

    protected static function setup(): ResponseModelCollectionConfig
    {
        throw new ResponseModelSetupException(static::class . " must override the setup method!");
    }

    public static function getResponseModelCollectionConfig(): ResponseModelCollectionConfig
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
        array $commandArgs = []
    ): ResponseModelCollection {
        return AsyncCollectionLoader::load(
            static::getResponseModelCollectionConfig(),
            $client,
            $commandArgs
        );
    }
    
    public static function load(
        ServiceClientInterface $client,
        array $commandArgs = []
    ): ResponseModelCollection {
        return CollectionLoader::load(
            static::getResponseModelCollectionConfig(),
            $client,
            $commandArgs
        );
    }

    public static function withData(
        ServiceClientInterface $client,
        array $data
    ): ResponseModelCollection {
        return WithDataCollectionLoader::load(
            static::getResponseModelCollectionConfig(),
            $client,
            [], //Don't need to supply command args as we already have the data for the model
            $data
        );
    }

    private function addResponse(ServiceClientInterface $client, array $responseData = [])
    {
        $config = static::getResponseModelCollectionConfig();
        /**
         * @var ResponseModel $model
         */
        $model = call_user_func(
            [$config->getChildResponseModelClass(), 'withData'],
            $client,
            $responseData
        );

        $this->add($model);
    }
}