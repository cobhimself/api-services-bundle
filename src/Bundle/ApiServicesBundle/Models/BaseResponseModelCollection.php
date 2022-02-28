<?php

namespace Cob\Bundle\ApiServicesBundle\Models;

use Cob\Bundle\ApiServicesBundle\Exceptions\ResponseModelSetupException;
use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\PostAddModelToCollectionEvent;
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
        //Initialize with zero elements
        parent::__construct([]);

        $this->client = $client;
        $this->loadPromise = $loadPromise;
        $this->loadState = $desiredLoadState;

        $config = static::getConfig();

        //Callback which adds to our collection based on response data
        $config->addInitCallback(function () {
            $this->finalizeChildrenData();
        });

        //We can go ahead and set the data for the model if it has already been loaded. Otherwise we wait until
        //the first time we attempt to get data.
        if ($desiredLoadState->isLoaded() || $desiredLoadState->isLoadedWithData()) {
            $this->data = new DotData($this->loadPromise->wait());
            static::getConfig()->doInits($this);
        }
    }

    protected function finalizeChildrenData()
    {
        $config = $this::getConfig();

        //If we've been loaded with data, we simply need to use the collection of data
        //we've been given. Otherwise, we need to use the collection path where we expect
        //the correct data to be upon obtaining the response.
        $dataPath = ($this->isLoadedWithData())
            ? ''
            : $config->getCollectionPath();
        $data = $this->getData()->dot($dataPath);
        foreach ($data as $child) {
            $this->addResponse($this->client, $child);
        }
    }

    protected static function setup(): ResponseModelCollectionConfig
    {
        throw new ResponseModelSetupException(static::class . " must override the setup method!");
    }

    public static function getConfig(): ResponseModelCollectionConfig
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
        array $countCommandArgs = []
    ): ResponseModelCollection {
        return AsyncCollectionLoader::load(
            static::getConfig(),
            $client,
            $commandArgs,
            $countCommandArgs
        );
    }
    
    public static function load(
        ServiceClientInterface $client,
        array $commandArgs = [],
        array $countCommandArgs = []
    ): ResponseModelCollection {
        return CollectionLoader::load(
            static::getConfig(),
            $client,
            $commandArgs,
            $countCommandArgs
        );
    }

    public static function withData(
        ServiceClientInterface $client,
        array $data = []
    ): ResponseModelCollection {
        return WithDataCollectionLoader::load(
            static::getConfig(),
            $client,
            [], //Don't need to supply command args as we already have the data for the model
            [], //Don't need to supply count command args either
            $data
        );
    }

    private function addResponse(ServiceClientInterface $client, array $responseData = [])
    {
        $config = static::getConfig();

        /**
         * @var ResponseModel $model
         */
        $model = call_user_func(
            [$config->getChildResponseModelClass(), 'withData'],
            $client,
            $responseData
        );

        $this->add($model);

        $client->dispatchEvent(
            PostAddModelToCollectionEvent::class,
            $config,
            $model
        );
    }

    public function count(): int
    {
        $this->confirmLoaded();

        return parent::count();
    }
}