<?php

namespace Cob\Bundle\ApiServicesBundle\Models;

use Cob\Bundle\ApiServicesBundle\Exceptions\ResponseModelSetupException;
use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\PostAddModelToCollectionEvent;
use Cob\Bundle\ApiServicesBundle\Models\Loader\AsyncCollectionLoader;
use Cob\Bundle\ApiServicesBundle\Models\Loader\CollectionLoader;
use Cob\Bundle\ApiServicesBundle\Models\Loader\Config\CollectionLoadConfig;
use Cob\Bundle\ApiServicesBundle\Models\Loader\Config\CollectionLoadConfigBuilder;
use Cob\Bundle\ApiServicesBundle\Models\Loader\Config\LoadConfigBuilder;
use Cob\Bundle\ApiServicesBundle\Models\Loader\LoadState;
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
        //Initialize with zero elements
        parent::__construct([]);

        if (!is_null($parent)) {
            $this->setParent($parent);
        }

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

    public static function loadAsync(CollectionLoadConfig $loadConfig): ResponseModelCollection {
        return AsyncCollectionLoader::load(
            static::getConfig(),
            $loadConfig
        );
    }
    
    public static function load(
        CollectionLoadConfig $loadConfig
    ): ResponseModelCollection {
        return CollectionLoader::load(
            static::getConfig(),
            $loadConfig
        );
    }

    public static function withData(
        CollectionLoadConfig $loadConfig
    ): ResponseModelCollection {
        return WithDataCollectionLoader::load(
            static::getConfig(),
            $loadConfig
        );
    }

    private function addResponse(ServiceClientInterface $client, array $responseData = [])
    {
        $config = static::getConfig();
        $childClass = $config->getChildResponseModelClass();

        /**
         * @var LoadConfigBuilder
         */
        $loadConfigBuilder = call_user_func([$childClass, 'using'], $client);

        /**
         * @var ResponseModel $model
         */
        $model = $loadConfigBuilder->withParent($this)->withData($responseData);

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

    public static function using(ServiceClient $client): CollectionLoadConfigBuilder
    {
        return CollectionLoadConfig::builder(static::class, $client);
    }

    public function get($key)
    {
        $this->confirmLoaded();

        return parent::get($key);
    }
}