<?php
/*
 * This file is part of the cobhimself/api-services-bundle package.
 *
 * (c) Collin D. Brooks <collin.brooks@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cob\Bundle\ApiServicesBundle\Models;

use Exception;
use GuzzleHttp\Command\CommandInterface;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Promise\PromiseInterface;
use Cob\Bundle\ApiServicesBundle\Exceptions\InvalidResponseModel;
use Cob\Bundle\ApiServicesBundle\Exceptions\ResponseModelException;
use Cob\Bundle\ApiServicesBundle\Exceptions\ResponseModelLoadCancelledException;
use Cob\Bundle\ApiServicesBundle\Exceptions\ResponseModelSetupException;
use Cob\Bundle\ApiServicesBundle\Exceptions\UnknownCommandException;
use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\AssociateParentModelEvent;
use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\ResponseModelPostExecuteCommandEvent;
use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\ResponseModelPostLoadEvent;
use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\ResponseModelPostLoadFromCacheEvent;
use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\ResponseModelPreExecuteCommandEvent;
use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\ResponseModelPreLoadEvent;
use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\ResponseModelPreLoadFromCacheEvent;
use Cob\Bundle\ApiServicesBundle\Models\ExceptionHandlers\ClientCommandExceptionHandler;
use Cob\Bundle\ApiServicesBundle\Models\ExceptionHandlers\ExceptionHandlerInterface;
use Cob\Bundle\ApiServicesBundle\Models\ExceptionHandlers\ResponseModelExceptionHandler;
use Cob\Bundle\ApiServicesBundle\Models\Http\RawResult;
use Cob\Bundle\ApiServicesBundle\Models\Util\Promise;
use Symfony\Component\EventDispatcher\Event;

/**
 * Abstract model all service client response models should extend from.
 *
 * This class has a couple of constants which are required to be set with sane
 * values in any class which extends it. No saneness checking is done if the
 * constants are accessed directly; use the static helper methods instead.
 */
abstract class AbstractResponseModel implements ResponseModelInterface, CommandLineOutputInterface, CommandLineStringHelpersInterface
{
    use CommandLineOutputTrait;
    use ResponseModelSetupTrait;

    /**
     * The service operation the model uses to load its data.
     */
    const LOAD_COMMAND = null;

    /**
     * @var array the arguments to send to the service operation
     */
    const LOAD_ARGUMENTS = [];

    /**
     * Key used when setting the raw data for a response model.
     */
    const RAW_DATA_KEY = '_raw_data';

    /**
     * Whether or not this response model only accepts raw data.
     */
    const RAW_DATA = false;

    /**
     * @var array an array of callbacks to be run upon initialization
     */
    protected $initCallbacks = [];

    /**
     * Contains a list of arguments used when loading the response model.
     *
     * @var array
     */
    protected $argsForCacheKey = [];

    /**
     * @var ServiceClient
     */
    protected $client;

    /**
     * Whether or not this response model has been loaded already.
     *
     * @var bool
     */
    protected $loaded = false;

    /**
     * Whether or not the data for this model was loaded from cache.
     *
     * @var bool
     */
    protected $loadedFromCache = false;

    /**
     * Whether or not this response model's data was populated with data
     * already retrieved
     *
     * @var bool
     */
    protected $loadedWithData = false;

    /**
     * Data associated with this model.
     *
     * @var array
     */
    protected $data;

    /**
     * Cache for our dot functionality.
     *
     * @var array
     */
    private $dotCache = [];

    /**
     * The parent response model or response model collection this response
     * model belongs to (if any).
     *
     * @var ResponseModelCollectionInterface|ResponseModelInterface|null
     */
    protected $parent;

    /**
     * Whether or not it was determined to cancel the loading of this model.
     *
     * @var bool
     */
    private $loadCancelled = false;

    /**
     * The reason, if any, the loading of this response model was cancelled.
     *
     * @var string
     */
    private $cancelReason = 'Loading was not cancelled!';

    /**
     * Construct the response model.
     *
     * @param array|null                                                   $data   data to set on the model initially
     * @param ResponseModelCollectionInterface|ResponseModelInterface|null $parent a parent collection for this
     *                                                                             response model
     * @param ServiceClient|null                                           $client the service client which will be used
     *                                                                             to load response data
     *
     * @throws InvalidResponseModel
     * @throws ResponseModelSetupException
     */
    public function __construct(
        array $data = null,
        $parent = null,
        ServiceClient $client = null
    ) {
        $this->data = $data ?? [];

        if (null !== $client) {
            $this->setClient($client);
            $this->inheritOutputFrom($client);
        }

        if (null !== $parent) {
            $this->doSetParent($parent);
        }

        $defaultCallback = $this->getDefaultInitCallback();
        if ($defaultCallback) {
            $this->addInitCallback($defaultCallback);
        }
    }

    /**
     * Set the parent for this model (or collection).
     *
     * @param $parent
     *
     * @throws ResponseModelSetupException
     * @throws InvalidResponseModel
     */
    protected function doSetParent($parent)
    {
        if (null !== $parent) {
            self::confirmValidResponseModel($parent);

            //Other external libraries may wish to know about the
            //parent association
            $this->dispatchEvent(
                AssociateParentModelEvent::class,
                $this,
                $parent
            );

            $this->setParent($parent);

            //Our own objects which extend this response model can also perform
            //additional processing when a parent model is associated
            $this->onAssociateParent($parent);
        }
    }

    /**
     * Get this model's parent model (or collection).
     *
     * @return ResponseModelCollectionInterface|ResponseModelInterface|null
     *
     * @throws ResponseModelSetupException
     */
    public function getParent()
    {
        $this->checkForPropertyException('parent');

        return $this->parent;
    }

    /**
     * @inheritDoc
     *
     * @returns ResponseModelInterface|ResponseModelCollectionInterface
     *
     * @throws InvalidResponseModel
     * @throws ResponseModelSetupException
     */
    public static function withData(
        ServiceClient $client,
        array $data = null,
        $parentModel = null
    ) {
        $model = new static($data, $parentModel, $client);
        $model->loadedWithData = true;

        $model->triggerCallbacks($model);

        return $model;
    }

    /**
     * {@inheritDoc}
     *
     * If a value for `$parent` is not sent in, automatic association
     * of parent Plan and parent Project models for created model will not
     * occur. However, when provided, any model which is generated will have
     * these links established.
     *
     * @throws InvalidResponseModel
     * @throws ResponseModelException
     * @throws ResponseModelSetupException
     */
    public static function getLoaded(
        ServiceClient $client,
        array $loadArguments = [],
        $parent = null,
        callable $initCallback = null,
        ExceptionHandlerInterface $handler = null,
        bool $clearCache = false
    ): ResponseModelInterface {
        $model = static::getUsingPromise(
            $client,
            $promise, //Filled through reference
            $parent,
            $loadArguments,
            $initCallback,
            $handler,
            $clearCache
        );

        //We've been asked to get the model already loaded so we've got to wait
        $promise->otherwise(function ($reason) use ($model) {
            throw new ResponseModelException(
                sprintf('Could not load model %s', get_class($model)),
                $reason
            );
        })->wait();

        //The model now has all the data it needs; return it
        return $model;
    }

    /**
     * {@inheritDoc}
     *
     * By creating a promise, we can delay the actual loading of the data for
     * this model to be done at a later time. This is helpful when we want to
     * perform bulk loading asynchronously.
     *
     * NOTE: The `$promise` variable sent in is set to the promise created by
     * this method and, for data to be loaded, its `wait()` method
     * MUST BE CALLED!
     *
     * @inheritDoc
     *
     * @return ResponseModelInterface
     *
     * @throws InvalidResponseModel
     * @throws ResponseModelSetupException
     * @noinspection ReturnTypeCanBeDeclaredInspection
     */
    public static function getUsingPromise(
        ServiceClient $client,
        PromiseInterface &$promise = null,
        $parent = null,
        array $loadArguments = [],
        callable $initCallback = null,
        ExceptionHandlerInterface $handler = null,
        bool $clearCache = false
    ) {
        /** @var ResponseModelInterface $model */
        $model = new static(null, $parent, $client);

        if (null !== $initCallback) {
            $model->addInitCallback($initCallback);
        }

        //Set the promise sent in so the caller can use it
        $promise = $model->load($loadArguments, $handler, $clearCache);

        return $model;
    }

    /**
     * {@inheritDoc}
     *
     * All loading is done through the use of Promises. The returned promise
     * should be waited on or placed in a queue which is waited on in order to
     * confirm all data has been loaded and set on the model.
     *
     * The promise's return value is the model itself.
     *
     * @throws Exception
     */
    public function load(
        array $commandArgs = [],
        ExceptionHandlerInterface $handler = null,
        bool $clearCache = false
    ): PromiseInterface {
        //No need to load more than once!
        if ($this->loaded) {
            return new FulfilledPromise($this);
        }

        return $this->getResponseDataLoadPromise($commandArgs)
            ->then(function ($responseData) {
                return $this->finalizeResponseData($responseData);
            })->otherwise(function ($reason) use ($handler) {
                $handler  = $handler
                    ?? ResponseModelExceptionHandler::passThruAndWrapWith(
                        ResponseModelException::class,
                        ['Unhandled response model exception!']
                    );

                //Our handler can modify the response.
                $response = $handler->handle($reason);

                //Did we modify the response and are we ok with that?
                if (null !== $response && is_array($response)) {
                    $this->setData($response);
                }

                return new FulfilledPromise($this);
            });
    }

    /**
     * @return bool
     */
    public function isLoaded(): bool
    {
        return $this->loaded;
    }

    /**
     * Dispatch an event using the client's dispatcher.
     *
     * @param string $eventClass the event name
     * @param mixed  ...$args    arguments to send to the event constructor
     *
     * @return Event
     *
     * @throws ResponseModelSetupException
     */
    protected function dispatchEvent(string $eventClass, ...$args): Event
    {
        return $this->getClient()->dispatchEvent($eventClass, ...$args);
    }

    /**
     * @inheritDoc
     *
     * @throws ResponseModelSetupException
     */
    public function getClient(): ServiceClient
    {
        $this->checkForPropertyException('client');

        return $this->client;
    }

    /**
     * @inheritDoc
     */
    public function setClient(ServiceClient $client)
    {
        $this->client = $client;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function triggerCallbacks(ResponseModelInterface $model)
    {
        foreach ($this->initCallbacks as $callback) {
            $callback($model);
        }
    }

    /**
     * @inheritDoc
     */
    public static function getLoadArguments(): array
    {
        static::checkForConstException('LOAD_ARGUMENTS');

        return static::LOAD_ARGUMENTS;
    }

    /**
     * @inheritDoc
     *
     * @throws ResponseModelSetupException
     */
    public static function getLoadCommand(): string
    {
        static::checkForConstException('LOAD_COMMAND');

        return static::LOAD_COMMAND;
    }

    /**
     * Whether or not our cache key exists within our cache.
     *
     * @param string $key Retrieve the cache for the given key. If null, this
     *                    method must obtain the cache key by other means.
     *
     * @throws ResponseModelSetupException
     */
    protected function isCached(string $key = null): bool
    {
        return $this->canCache()
            && $this->getClient()->getCache()->contains($this->getCacheKey());
    }

    /**
     * Determine whether or not this class can be cached.
     *
     * @throws ResponseModelSetupException if we are unable to get the
     *                                     service client
     */
    protected function canCache(): bool
    {
        return null !== $this->getClient()->getCache();
    }

    /**
     * Return the key to use when caching.
     */
    protected function getCacheKey(): string
    {
        return get_class($this) . static::hashArray($this->argsForCacheKey);
    }

    /**
     * Return a unique hash string for the given array.
     */
    protected static function hashArray(array $array): string
    {
        return md5(serialize($array));
    }

    /**
     * Set the cache data for the given key.
     *
     * Having the key as the second parameter allows us to utilize this
     * interface in classes where the cache key is obtained in other ways.
     *
     * @param mixed  $data the data to set in the cache
     * @param string $key  Check the cache for the given key. If null, this
     *                     method must obtain the cache key by other means.
     *
     * @throws ResponseModelSetupException if the cache key for this response
     *                                     model has not been set
     */
    protected function setCache($data, string $key = null)
    {
        if ($this->canCache()) {
            $this->getClient()->getCache()->save($this->getCacheKey(), $data);
        }
    }

    /**
     * Get the cache associated with our cache key.
     *
     * @param string $key Retrieve the cache for the given key. If null, this
     *                    method must obtain the cache key by other means.
     *
     * @return false|mixed if we do not have cache for our cache key,
     *                     return false
     *
     * @throws ResponseModelSetupException if the cache key has not been setup
     *                                     for this response model
     */
    protected function getCache(string $key = null)
    {
        if ($this->canCache()) {
            return $this->getClient()->getCache()->fetch($this->getCacheKey());
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function dot(string $key, $default = false, $data = null)
    {
        $firstRun = null === $data;

        $data = $data ?? $this->getData();

        if ($key === '') {
            return $data;
        }

        if (empty($data)) {
            return $default;
        }

        //No need to check our dotCache or move forward if we aren't
        //looking for sub-data.
        if (strpos($key, '.') === false) {
            return $data[$key] ?? $default;
        }

        //Have we traversed this path before?
        if (array_key_exists($key, $this->dotCache)) {
            return $this->dotCache[$key];
        }

        //Recurse to get our data
        $parts = explode('.', $key);
        $head = array_shift($parts);
        $tail = implode('.', $parts);
        $value = $this->dot($tail, $default, $data[$head]);

        //Add to our dot cache if we're back at our first run of this method.
        if ($firstRun) {
            $this->dotCache[$key] = $value ?? $default;
        }

        return $value ?? $default;
    }

    /**
     * Return the array representation of a dot path.
     *
     * @param string $path  the dot path
     * @param mixed  $value the final value of this dot path
     *
     * @return array
     */
    public static function dotToArray(string $path, $value): array
    {
        $final = [];
        $period = strpos($path, '.');

        if ($period === false) {
            $final[$path] = $value;
        } else {
            $head = substr($path, 0, $period);
            $tail = substr($path, $period + 1);
            $final[$head] = static::dotToArray($tail, $value);
        }

        return $final;
    }

    /**
     * @inheritDoc
     */
    public function getData(): array
    {
        return $this->data ?? [];
    }

    /**
     * @inheritDoc
     */
    public function setData($data, string $key = null): ResponseModelInterface
    {
        if (null === $key) {
            $this->data = $data;
            $this->dotCache = [];
        } else {
            $this->data[$key] = $data;

            //We need to remove any previous dot cache associated with this key
            foreach ($this->dotCache as $cacheKey => $value) {
                $pos = strpos($cacheKey, $key);
                if ($pos !== false && $pos === 0) {
                    unset($this->dotCache[$cacheKey]);
                }
            }
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setRawData($data): ResponseModelInterface
    {
        $this->setData($data, self::RAW_DATA_KEY);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getRawData()
    {
        return $this->dot(self::RAW_DATA_KEY);
    }

    /**
     * @inheritDoc
     */
    public function addInitCallback(callable $initCallback)
    {
        $this->initCallbacks[] = $initCallback;
    }

    /**
     * Return a callback which should be called for this model once it is
     * instantiated or loaded.
     *
     * If overridden by a ResponseModelCollectionInterface, the init method is
     * called for each model within the collection as it is instantiated.
     *
     * @return void|callable A function whose only argument will be an instance
     *                       of this response model. Any return values are
     *                       ignored. If void, no init callback is added.
     */
    protected function getDefaultInitCallback()
    {
    }

    /**
     * {@inheritDoc}
     *
     * @param ResponseModelCollectionInterface|ResponseModelInterface $parent
     *
     * @return ResponseModelInterface this response model
     *
     * @throws InvalidResponseModel if the parent does not implement
     *                              ResponseModelInterface or
     *                              ResponseModelCollectionInterface
     */
    public function setParent($parent)
    {
        self::confirmValidResponseModel($parent);

        $this->parent = $parent;

        return $this;
    }

    /**
     * Whether or not the loading of this response model was cancelled.
     *
     * @return bool
     */
    public function wasLoadingCancelled(): bool
    {
        return $this->loadCancelled;
    }

    /**
     * Set whether or not this model's loading was cancelled.
     *
     * @param bool $loadCancelled
     *
     * @return $this
     */
    protected function setLoadCancelled(bool $loadCancelled): self
    {
        $this->loadCancelled = $loadCancelled;

        return $this;
    }

    /**
     * @return string
     */
    public function getCancelReason(): string
    {
        return $this->cancelReason;
    }

    /**
     * Provide context as to why this response model's loading was cancelled.
     *
     * @param string $cancelReason
     *
     * @return $this
     */
    protected function setCancelReason(string $cancelReason): self
    {
        $this->cancelReason = $cancelReason;

        return $this;
    }

    /**
     * Setup a sane default exception handler for use with our loading method.
     *
     * We're going to pass through any exception by default. This means any
     * connection issues which we might be ok with swallowing will be passed
     * through. @see ClientCommandExceptionHandler for ways to handle specific
     * HTTP error codes.
     *
     * @return ResponseModelExceptionHandler|ExceptionHandlerInterface
     */
    protected function getDefaultExceptionHandler()
    {
        return ResponseModelExceptionHandler::passThruAndWrapWith(
            ResponseModelException::class,
            [sprintf('Could not load response model %s', static::class)]
        );
    }

    /**
     * Get the command for this response model with the given command args.
     *
     * @param array $commandArgs the arguments to provide the command
     *
     * @return CommandInterface
     *
     * @throws ResponseModelSetupException
     * @throws UnknownCommandException
     */
    public static function getCommand(
        ServiceClient $client,
        array $commandArgs
    ): CommandInterface {
        return $client->getCommand(static::getLoadCommand(), $commandArgs);
    }

    /**
     * Get a promise which will be fulfilled with the response data from our
     * command's execution.
     *
     * @param CommandInterface $command the command to execute asynchronously
     *
     * @return PromiseInterface a promise which, upon resolution, will have the
     *                          response data as the value
     */
    private function getExecutePromise(
        CommandInterface $command
    ): PromiseInterface {
        return Promise::async(function () use ($command) {
            $this->dispatchEvent(
                ResponseModelPreExecuteCommandEvent::class,
                $this,
                $command
            );

            return $this->getClient()
                ->executeAsync($command)
                ->then(function ($response) use ($command) {
                    /* @var ResponseModelPostExecuteCommandEvent $event */
                    $event = $this->dispatchEvent(
                        ResponseModelPostExecuteCommandEvent::class,
                        $this,
                        $command,
                        $response
                    );
                    //Use the response from the dispatched event
                    $response = $event->getResponse();

                    return new FulfilledPromise($response);
                });
        });
    }

    /**
     * Get the response data for the given command from cache.
     *
     * @param CommandInterface $command the command which this model runs
     *
     * @return PromiseInterface a promise set to be fulfilled by default with
     *                          the response data from cache
     */
    private function getCacheDataPromise(
        CommandInterface $command
    ): PromiseInterface {
        return Promise::async(function () use ($command) {
            $this->dispatchEvent(
                ResponseModelPreLoadFromCacheEvent::class,
                $this,
                $command
            );

            $responseData = $this->getCache();

            /* @var ResponseModelPostLoadFromCacheEvent $event */
            $event = $this->dispatchEvent(
                ResponseModelPostLoadFromCacheEvent::class,
                $this,
                $command,
                $responseData
            );

            $responseData = $event->getCachedData();

            $this->loadedFromCache = true;

            return $responseData;
        });
    }

    /**
     * Finalize our loading of the given response data into the model.
     *
     * @param RawResult|array $responseData the response data from the client
     *
     * @return PromiseInterface the fulfilled promise which is resolved with a
     *                          reference to this model
     *
     * @throws ResponseModelSetupException
     */
    private function finalizeResponseData($responseData)
    {
        if ($responseData instanceof RawResult) {
            if (!static::RAW_DATA) {
                throw new ResponseModelSetupException(sprintf(
                    'Unexpected raw data! Set RAW_DATA to true for %s if it expects data to not be deserialized!',
                    static::class
                ));
            }

            $this->setRawData((string) $responseData);
        } else {
            $this->setData($responseData);
        }

        $this->triggerCallbacks($this);

        $this->setCache($this->getData());

        $this->dispatchEvent(
            ResponseModelPostLoadEvent::class,
            $this
        );

        $this->loaded = true;

        return new FulfilledPromise($this);
    }

    /**
     * Get the promise which handles obtaining the response data.
     *
     * @param array $commandArgs the command arguments to use when running
     *                           the command
     *
     * @return PromiseInterface a promise which, upon fulfillment, will return
     *                          the response data for us to use
     */
    private function getResponseDataLoadPromise(
        array $commandArgs,
        bool $clearCache = false
    ): PromiseInterface {
        return Promise::async(function () use ($commandArgs, $clearCache) {
            /* @var ResponseModelPreLoadEvent $event */
            $event = $this->dispatchEvent(
                ResponseModelPreLoadEvent::class,
                $this,
                $commandArgs,
                $clearCache
            );

            if ($event->loadCancelled()) {
                $this->setLoadCancelled(true);

                if ($event->failOnCancel()) {
                    throw new ResponseModelLoadCancelledException(
                        $this,
                        $commandArgs,
                        $clearCache,
                        $event->getCancelReason()
                    );
                }

                $this->setCancelReason($event->getCancelReason());

                //Even though we are cancelling the loading, we'll leave others
                //to determine how to handle it.
                return new FulfilledPromise([]);
            }

            //Our event could have updated our command args and whether or not
            //we want to clear the cache.
            $commandArgs = $event->getCommandArgs();
            $clearCache = $event->doClearCache();

            //Merge our command arguments with our default load arguments
            $commandArgs = array_merge(
                static::getLoadArguments(),
                $commandArgs
            );

            //Make sure our cache key takes all of our arg values into account.
            $this->argsForCacheKey = $commandArgs;

            if ($clearCache) {
                $this->getClient()->getCache()->delete($this->getCacheKey());
            }

            //Get the command for this response model
            $command  = static::getCommand($this->getClient(), $commandArgs);

            //Return a promise which is fulfilled with the final response data.
            return (!$this->isCached())
                ? $this->getExecutePromise($command)
                : $this->getCacheDataPromise($command);
        });
    }

    /**
     * Method which should be overwritten by child classes if additional steps
     * should be performed when a parent is associated with this model.
     *
     * @param ResponseModelInterface|ResponseModelCollectionInterface $parent
     */
    protected function onAssociateParent($parent)
    {
    }

    public function wasLoadedFromCache(): bool
    {
        return $this->loadedFromCache;
    }

    public function wasLoadedWithData(): bool
    {
        return $this->loadedWithData;
    }

    /**
     * Add the given path to our client's service description's base URI
     *
     * @param string $path the path, after the base URI, not including the
     *                     leading /
     *
     * @return string
     *
     * @throws ResponseModelSetupException
     */
    protected function constructServerLink(string $path): string
    {
        return $this->getClient()->getDescription()->getBaseUri() . '/' . $path;
    }

    public function __toString()
    {
        $output = static::class . PHP_EOL;
        $output .= 'Data:' . PHP_EOL;
        $output .= static::outputStructure($this->getData());

        return $output;
    }
}
