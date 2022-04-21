<?php

namespace Cob\Bundle\ApiServicesBundle\Models\Response\Collection;

use Cob\Bundle\ApiServicesBundle\Exceptions\ResponseModelSetupException;
use Cob\Bundle\ApiServicesBundle\Models\Config\ResponseModelCollectionConfig;
use Cob\Bundle\ApiServicesBundle\Models\Config\ResponseModelCollectionConfigBuilder;
use Cob\Bundle\ApiServicesBundle\Models\DotData;
use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\PostAddModelToCollectionEvent;
use Cob\Bundle\ApiServicesBundle\Models\Loader\AsyncCollectionLoader;
use Cob\Bundle\ApiServicesBundle\Models\Loader\CollectionLoader;
use Cob\Bundle\ApiServicesBundle\Models\Loader\Config\CollectionLoadConfig;
use Cob\Bundle\ApiServicesBundle\Models\Loader\Config\CollectionLoadConfigBuilder;
use Cob\Bundle\ApiServicesBundle\Models\Loader\Config\LoadConfig;
use Cob\Bundle\ApiServicesBundle\Models\Loader\Config\LoadConfigBuilder;
use Cob\Bundle\ApiServicesBundle\Models\Loader\LoadState;
use Cob\Bundle\ApiServicesBundle\Models\Loader\WithDataCollectionLoader;
use Cob\Bundle\ApiServicesBundle\Models\Response\HasParentTrait;
use Cob\Bundle\ApiServicesBundle\Models\Response\ResponseModel;
use Cob\Bundle\ApiServicesBundle\Models\Response\ResponseModelTrait;
use Cob\Bundle\ApiServicesBundle\Models\ServiceClientInterface;
use Cob\Bundle\ApiServicesBundle\Models\Util\LogUtil;
use Doctrine\Common\Collections\ArrayCollection;
use Generator;
use GuzzleHttp\Promise\PromiseInterface;

/**
 * Base response model all response model collections should extend from.
 */
class BaseResponseModelCollection
    extends ArrayCollection
    implements ResponseModelCollection
{
    use ResponseModelTrait;
    use HasParentTrait;

    /**
     * Establish a new response model collection with a specific load state and load promise.
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

        //We can go ahead and set the data for the model if it has already been loaded. Otherwise we wait until
        //the first time we attempt to get data.
        if ($desiredLoadState->isLoaded() || $desiredLoadState->isLoadedWithData()) {
            $this->data = new DotData($this->loadPromise->wait());
            $this->finalizeData();
            static::getConfig()->doInits($this);
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function finalizeData()
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

    /**
     * Setup a configuration builder this model will use when configuring base functionality.
     *
     * @return ResponseModelCollectionConfigBuilder the configuration builder which will be used upon
     *                                              model instantiation.
     */
    protected static function setup(): ResponseModelCollectionConfigBuilder
    {
        throw new ResponseModelSetupException(static::class . " must override the setup method!");
    }

    /**
     * {@inheritDoc}
     */
    public static function getConfig(): ResponseModelCollectionConfig
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

    /**
     * {@inheritDoc}
     *
     * @see AsyncCollectionLoader::load() for details on how the data is loaded.
     */
    public static function loadAsync(CollectionLoadConfig $loadConfig): ResponseModelCollection {
        static::logLoad('async', $loadConfig);
        return AsyncCollectionLoader::load(
            static::getConfig(),
            $loadConfig
        );
    }

    /**
     * {@inheritDoc}
     *
     * @see CollectionLoader for details on how the data is loaded.
     */
    public static function load(
        CollectionLoadConfig $loadConfig
    ): ResponseModelCollection {
        static::logLoad('load', $loadConfig);
        return CollectionLoader::load(
            static::getConfig(),
            $loadConfig
        );
    }

    /**
     * {@inheritDoc}
     *
     * @see WithDataCollectionLoader for details on how the data is loaded.
     */
    public static function withData(
        CollectionLoadConfig $loadConfig
    ): ResponseModelCollection {
        static::logLoad('withData', $loadConfig);
        return WithDataCollectionLoader::load(
            static::getConfig(),
            $loadConfig
        );
    }

    /**
     * Add data to this collection through the given response data.
     *
     * Details on how to load data from the given response data are handled through this model's
     * {@link ResponseModelCollectionConfig}.
     *
     * Upon successfully creating the child model, the {@link PostAddModelToCollectionEvent} is dispatched.
     *
     * @param ServiceClientInterface $client       the service client
     * @param array                  $responseData the response data to use for obtaining child model data
     */
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

    /**
     * Obtain a count of the child response models in this collection.
     *
     * NOTE: This forces the data to be loaded if not already!
     *
     * @return int
     */
    public function count(): int
    {
        $this->confirmLoaded();

        return parent::count();
    }

    /**
     * Quickly obtain a collection load configuration builder for this response model collection.
     *
     * This method is the preferred way to instantiate new response model collections.
     *
     * @param ServiceClientInterface $client the service client used to load data
     *
     * @return CollectionLoadConfigBuilder the load configuration builder used to specify load details for the response
     *                                     model collection
     */
    public static function using(ServiceClientInterface $client): CollectionLoadConfigBuilder
    {
        return CollectionLoadConfig::builder(static::class, $client);
    }

    /**
     * Get a response model from this collection at the given index key.
     *
     * NOTE: This causes the collection's data to be loaded!
     *
     * @param int $key the index key of the child model to return.
     *
     * @return ResponseModel|null the response model or null if none exist at the index.
     */
    public function get($key)
    {
        $this->confirmLoaded();

        return parent::get($key);
    }

    /**
     * Generator which yields each item in this collection, optionally only
     * those deemed acceptable.
     *
     * @param callable $accept callback whose only argument is an item in this
     *                         collection and returns true or false as to
     *                         whether or not the item is acceptable to yield
     *
     * @return Generator
     */
    public function yield(callable $accept = null): Generator {
        foreach ($this as $model) {
            if (null === $accept || $accept($model) === true) {
                yield $model;
            }
        }
    }

    /**
     * Run a callback on each item in this collection; optionally only for
     * acceptable items.
     *
     * @param callable      $do         callback run on each acceptable item in
     *                                  this collection; returned result
     *                                  is ignored
     * @param callable|null $acceptable callback run on each item in this
     *                                  collection to determine whether or not
     *                                  we want to do anything with it
     */
    public function each(callable $do, callable $acceptable = null)
    {
        foreach ($this->yield($acceptable) as $item) {
            $do($item);
        }
    }

    /**
     * Get the results from a callback function for each of the items in this
     * collection; optionally only for acceptable items.
     *
     * @param callable      $do         callback run on each acceptable item in
     *                                  this collection; returned result
     *                                  is aggregated
     * @param callable|null $acceptable callback run on each item in this
     *                                  collection to determine whether or not
     *                                  we want to do anything with it
     *
     * @return ResponseModel[] the aggregate results
     */
    public function eachGet(callable $do, callable $acceptable = null): array
    {
        $results = [];

        foreach ($this->yield($acceptable) as $item) {
            $results[] = $do($item);
        }

        return $results;
    }

    /**
     * Like eachGet but the returned array is flattened.
     *
     * @param callable      $do         callback run on each acceptable item in
     *                                  this collection; returned result
     *                                  is aggregated
     * @param callable|null $acceptable callback run on each item in this
     *                                  collection to determine whether or not
     *                                  we want to do anything with it
     *
     * @return array the flattened aggregate results
     */
    public function eachGetFlat(
        callable $do,
        callable $acceptable = null
    ): array {
        return static::flattenArray($this->eachGet($do, $acceptable));
    }

    public static function flattenArray(array $array): array
    {
        $final = [];

        array_walk_recursive($array, static function ($a) use (&$final) {
            $final[] = $a;
        });

        return $final;
    }

    /**
     * Apply a user supplied function to every member of the collection.
     *
     * NOTE: If callback needs to be working with the actual values of the
     * array, specify the first parameter of callback as a reference. Then, any
     * changes made to those elements will be made in the original array itself.
     *
     * @see array_walk()
     *
     * @param mixed|null $userData data to send in to each fun
     *
     * @param callable   $walk     the function to call for each collection item
     *
     * @return $this
     */
    public function walk(callable $walk, $userData = null): self
    {
        array_walk($this, $walk, $userData);

        return $this;
    }

    /**
     * Walk through our collection and return the first item the callback
     * function returns true for.
     *
     * @param callable $accept function passed each item in the collection
     *                         iteratively;
     *
     * @return false|ResponseModel the first model we accept or false if none are accepted
     */
    public function returnFirst(callable $accept)
    {
        foreach ($this->yield($accept) as $model) {
            return $model;
        }

        return false;
    }

    /**
     * Filter the items in this collection and return the first one.
     *
     * @param callable $filter a callable which accepts a single item from this
     *                         collection and determines whether or not it
     *                         should be included in the final list of items
     *
     * @return false|ResponseModel[]
     */
    protected function filterAndReturnFirst(callable $filter)
    {
        $items = $this->filter($filter)->toArray();

        return (count($items)) ? array_shift($items) : false;
    }

    public function reduce(callable $filter): BaseResponseModelCollection
    {
        $filtered = array_values($this->filter($filter)->toArray());

        $this->clear();

        foreach ($filtered as $child) {
            $this->add($child);
        }

        return $this;
    }

    /**
     * Log load configuration details.
     *
     * This is helpful for debugging.
     *
     * @param string               $strategy   a string representing the strategy being used to load data
     * @param CollectionLoadConfig $loadConfig the load configuration to log details about.
     */
    protected static function logLoad(string $strategy, CollectionLoadConfig $loadConfig) {
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
