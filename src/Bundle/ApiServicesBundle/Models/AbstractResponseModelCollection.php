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

use Generator;
use GuzzleHttp\Command\Command;
use Cob\Bundle\ApiServicesBundle\Exceptions\IncorrectParentResponseModel;
use Cob\Bundle\ApiServicesBundle\Exceptions\InvalidCollectionItem;
use Cob\Bundle\ApiServicesBundle\Exceptions\InvalidResponseModel;
use Cob\Bundle\ApiServicesBundle\Exceptions\ResponseModelCollectionException;
use Cob\Bundle\ApiServicesBundle\Exceptions\ResponseModelSetupException;
use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\CommandFulfilledEvent;
use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\PostAddDataFromParentEvent;
use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\PostAddFromResponsesEvent;
use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\PostAddModelToCollectionEvent;
use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\PostCountEvent;
use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\PostExecuteCommandsEvent;
use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\PostLoadEvent;
use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\PostLoadFromCacheEvent;
use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\PreAddDataFromParentEvent;
use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\PreAddFromResponsesEvent;
use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\PreCountEvent;
use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\PreExecuteCommandsEvent;
use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\PreLoadEvent;
use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\PreLoadFromCacheEvent;
use Cob\Bundle\ApiServicesBundle\Models\ExceptionHandlers\ExceptionHandlerInterface;
use Cob\Bundle\ApiServicesBundle\Models\ExceptionHandlers\ResponseModelExceptionHandler;
use Cob\Bundle\ApiServicesBundle\Models\Util\Promise;

use Countable;
use Exception;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Promise\PromiseInterface;
use Iterator;

/**
 * Abstract model all service client response collection models should
 * extend from.
 *
 * This class has a couple of constants which are required to be set with sane
 * values in any class which extends it. No saneness checking is done if the
 * constants are accessed directly; use the static helper methods instead.
 */
abstract class AbstractResponseModelCollection extends AbstractResponseModel implements
    ResponseModelCollectionInterface,
    Iterator,
    Countable
{
    use ResponseModelSetupTrait;
    use CollectionTrait;
    use CountTrait;

    /**
     * The service command to use when retrieving count information.
     *
     * If null, attempts to get the count command will throw
     * a ResponseModelSetupException.
     *
     * @var string
     */
    const COUNT_COMMAND = null;

    /**
     * @var array the arguments to send the load command in order to obtain a
     *            count of total items in the collection
     */
    const COUNT_ARGUMENTS = [];

    /**
     * The dot path to the array of child items for this collection.
     *
     * If null, attempts to get the collection key will throw
     * a ResponseModelSetupException.
     *
     * @see ResponseModelInterface::dot()
     *
     * @var string
     */
    const COLLECTION_KEY = null;

    /**
     * The fully-qualified class name of the class which represents an item in
     * this collection.
     *
     * If null, attempts to get the collection class will throw
     * a ResponseModelSetupException.
     *
     * @var string
     */
    const COLLECTION_CLASS = null;

    /**
     * The dot value path where the data required to initialize the collection
     * resides in the response data from the COUNT_COMMAND. Size and  max result
     * data will be obtained from this data in order to create the commands
     * necessary to load this collection in its entirety in chunks.
     *
     * If null, attempts to get the count value path will throw
     * a ResponseModelSetupException.
     *
     * @example if our data was:
     * [
     *   'one' => [
     *     'two' => [
     *       'size' => 100,
     *       'max-results' => 25,
     *       'start-index' => 0
     *     ]
     *   ]
     * ]
     * this should be 'one.two'
     *
     *
     * @see     AbstractResponseModelCollection::getMaxResultsToLoad()
     * @see     AbstractResponseModelCollection::getGenerateCountArgsCallback()
     * @see     AbstractResponseModelCollection::getSize()
     * @see     ServiceClient::getChunkedCommands()
     * @see     ResponseModelInterface::dot()
     *
     * @var string
     */
    const COUNT_VALUE_PATH = null;

    /**
     * When splitting up the requests used to construct the entire collection,
     * how many results should be returned by our LOAD_COMMAND at a time?
     * Defaults to 150.
     */
    const LOAD_MAX_RESULTS = 150;

    /**
     * The current id for use with our \Iterator methods.
     *
     * @see \Iterator
     *
     * @var int
     */
    private $pointer = 0;

    /**
     * An array containing all of the responses received for loading
     *
     * @var array
     */
    private $responses = [];

    /**
     * Contains each of the response models within this collection of
     * response models.
     *
     * @var array[ResponseModelInterface]
     */
    private $collection = [];

    /**
     * Construct a response model collection.
     *
     * @param array|null                  $data        the data to set on the
     *                                                 response model collection
     * @param ResponseModelInterface|null $parentModel the parent model to
     *                                                 associate with this
     *                                                 collection
     * @param ServiceClient|null          $client      the service client to use
     *                                                 when loading data into
     *                                                 this collection
     *
     * @throws ResponseModelCollectionException
     */
    public function __construct(
        array $data = null,
        ResponseModelInterface $parentModel = null,
        ServiceClient $client = null
    ) {
        try {
            parent::__construct($data, $parentModel, $client);

            $this->add($this->data);
        } catch (Exception $e) {
            throw new ResponseModelCollectionException(
                sprintf('Unable to construct %s because:' . PHP_EOL, static::class),
                $e
            );
        }
    }

    /**
     * @inheritDoc
     *
     * @throws ResponseModelCollectionException
     */
    public function add(array $responseData = null)
    {
        try {
            //No need to add if there isn't any data.
            if (!empty($responseData)) {
                $class = static::getCollectionClass();
                $items = $this->dot(
                    static::getCollectionKey(),
                    [],
                    $responseData
                );

                //We'll either construct our collection items into the class
                //our collection class model expects OR we will confirm the
                //items are already the correct class
                foreach ($items as $item) {
                    if (is_object($item)) {
                        $itemClass = get_class($item);
                        if ($itemClass !== $class) {
                            throw new InvalidCollectionItem($this, $itemClass);
                        }
                        $itemObj = $item;
                    } else {
                        $itemObj = new $class($item, $this, $this->getClient());
                    }

                    $this->triggerCallbacks($itemObj);
                    $this->dispatchEvent(
                        PostAddModelToCollectionEvent::class,
                        $itemObj,
                        $this
                    );

                    $this->collection[] = $itemObj;
                }
            }
        } catch (ResponseModelSetupException $e) {
            throw new ResponseModelCollectionException(
                'Could not add response data!',
                $e
            );
        }
    }

    /**
     * @inheritDoc
     *
     * @throws ResponseModelSetupException
     */
    public static function getCollectionClass(): string
    {
        static::checkForConstException('COLLECTION_CLASS');

        return static::COLLECTION_CLASS;
    }

    /**
     * @inheritDoc
     *
     * @throws ResponseModelSetupException
     */
    public static function getCollectionKey(): string
    {
        static::checkForConstException('COLLECTION_KEY');

        return static::COLLECTION_KEY;
    }

    /**
     * @inheritDoc
     */
    public function reduce(callable $filter): ResponseModelCollectionInterface
    {
        $this->collection = array_values($this->filter($filter));

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function filter(callable $filter): array
    {
        return iterator_to_array(new CollectionFilter(
            $this->yield(),
            $filter
        ), false);
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
    public function yield(callable $accept = null)
    {
        foreach ($this as $model) {
            if (null === $accept) {
                yield $model;
            } elseif ($accept($model) === true) {
                yield $model;
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function clear(): ResponseModelCollectionInterface
    {
        $this->collection = [];

        return $this;
    }

    /**
     * Create a new collection model using an already constructed array of
     * collection items.
     *
     * @param array         $collection
     * @param ServiceClient $client
     * @param null          $parentModel
     *
     * @throws InvalidCollectionItem
     * @throws ResponseModelCollectionException
     */
    public static function fromCollection(
        array $collection,
        ServiceClient $client,
        $parentModel = null
    ): ResponseModelCollectionInterface {
        $model = new static([], $parentModel, $client);
        $model->validateCollection($collection)->setCollection($collection);

        return $model;
    }

    private function setCollection(array $collection)
    {
        $this->collection = $collection;
    }

    /**
     * Confirm the items within this collection match up with what this
     * collection says it should contain.
     *
     * @param array $collection
     *
     * @return $this
     *
     * @throws InvalidCollectionItem
     */
    private function validateCollection(array $collection): self
    {
        foreach ($collection as $item) {
            if (!static::isInstanceOf($item, static::COLLECTION_CLASS)) {
                throw new InvalidCollectionItem($this, static::className($item));
            }
        }

        return $this;
    }

    /**
     * @inheritDoc
     *
     * @throws ResponseModelCollectionException
     */
    public static function empty(
        ResponseModelInterface $parentModel = null,
        ServiceClient $client = null
    ): ResponseModelCollectionInterface {
        return new static(null, $parentModel, $client);
    }

    /**
     * @inheritDoc
     *
     * @throws ResponseModelCollectionException
     * @throws ResponseModelSetupException
     * @throws InvalidResponseModel
     */
    public static function withDataFromParent(
        ResponseModelInterface $parentModel,
        string $dataKey,
        callable $initCallback = null,
        bool $requireData = false
    ): ResponseModelCollectionInterface {
        //Use withData in order to set loadedWithData to true
        $collection = static::withData($parentModel->getClient(), null, $parentModel);

        //Upon each of our collection's models being initialized, set their
        //parent collection
        $collection->addInitCallback(
            function (ResponseModelInterface $model) use ($collection) {
                $model->setParent($collection);
            }
        );

        //Obtain the collection-specific data from the parent model's data.
        $data = $parentModel->dot($dataKey, []);

        //Set the data for the collection model
        $collection->setData($data);

        //Uh oh, we were expecting data to be available!
        if ($requireData && empty($data)) {
            throw new ResponseModelCollectionException(
                sprintf(
                    'Cannot instantiate %s because %s has no data at \'%s\'!',
                    static::class,
                    get_class($parentModel),
                    $dataKey
                )
            );
        }

        $collection->validateData($data);

        //Add the callback the user provided.
        if (null !== $initCallback) {
            $collection->addInitCallback($initCallback);
        }

        $parentModel->getClient()->dispatchEvent(
            PreAddDataFromParentEvent::class,
            $parentModel,
            $collection,
            $data
        );

        //Setup our collection data if we have it
        if ($data) {
            $collection->add($data);
        }

        $parentModel->getClient()->dispatchEvent(
            PostAddDataFromParentEvent::class,
            $parentModel,
            $collection,
            $data
        );

        $collection->loaded = true;

        return $collection;
    }

    /**
     * Validate the data passed in directly to our collection.
     *
     * This makes sure we will have no issue obtaining the data later.
     *
     * @param array $data the data to validate
     *
     * @throws ResponseModelCollectionException
     * @throws ResponseModelSetupException
     */
    protected function validateData(array $data = null)
    {
        if (!empty($data)) {
            try {
                $collectionKey = static::getCollectionKey();
            } catch (ResponseModelSetupException $e) {
                throw new ResponseModelCollectionException(
                    'Could not validate given data!',
                    $e
                );
            }

            $count = $this->getSize();

            if (
                false !== $count
                && $count > 0
                && count($this->dot($collectionKey)) !== $count
            ) {
                throw new ResponseModelCollectionException(
                    'Collection count and collection size differ!'
                );
            }
        }
    }

    /**
     * @inheritDoc
     *
     * @throws ResponseModelSetupException
     */
    public function getSize(): int
    {
        throw new ResponseModelSetupException('You must override ' . __FUNCTION__ . ' in order to use it!');
    }

    public function all(): array
    {
        return $this->collection;
    }

    /**
     * @inheritDoc
     *
     * @throws ResponseModelCollectionException
     */
    public static function getUsingPromise(
        ServiceClient $client,
        PromiseInterface &$promise = null,
        $parent = null,
        array $loadArguments = [],
        callable $initCallback = null,
        ExceptionHandlerInterface $handler = null,
        bool $clearCache = false
    ): ResponseModelCollectionInterface {
        /** @var ResponseModelCollectionInterface $collection */
        $collection = new static([], $parent, $client);

        //Upon each of our collection's models being initialized, set their
        //parent collection
        $collection->addInitCallback(
            function (ResponseModelInterface $model) use ($collection) {
                $model->setParent($collection);
            }
        );

        if (null !== $initCallback) {
            $collection->addInitCallback($initCallback);
        }

        $promise = $collection->load($loadArguments, $handler, $clearCache);

        return $collection;
    }

    /**
     * {@inheritDoc}
     *
     * The returned promise MUST have its `wait` method called in order to
     * confirm the loaded data is finalized.
     */
    public function load(
        array $commandArgs = [],
        ExceptionHandlerInterface $handler = null,
        bool $clearCache = false
    ): PromiseInterface {
        //No need to do anything if we've already loaded data.
        if ($this->loaded) {
            return new FulfilledPromise($this);
        }

        return Promise::async(function () use ($commandArgs, $handler, $clearCache) {
            //Setup our cache key to use all command arguments
            $this->argsForCacheKey = array_merge(
                static::getLoadArguments(),
                $commandArgs
            );

            if ($clearCache && $this->isCached($this->getCacheKey())) {
                $this->getClient()->getCache()->delete($this->getCacheKey());
            }

            $this->dispatchEvent(PreLoadEvent::class, $this);
            $cached = $this->attemptLoadFromCache();
            if ($cached) {
                return $this;
            }
            //Make sure we have our count data.
            $this->initialize($commandArgs);

            if ($this->getSize() > 0) {
                try {
                    //Chunk our commands so we can send multiple requests
                    //for data at a time.
                    $commands = $this->client->getChunkedCommands(
                        static::getLoadCommand(),
                        array_merge(static::getLoadArguments(), $commandArgs),
                        $this->getSize(),
                        $this->getGenerateCountArgsCallback(),
                        $this->getMaxResultsToLoad()
                    );
                } catch (ResponseModelSetupException $e) {
                    throw new ResponseModelCollectionException(
                        'Could not get chunked commands!',
                        $e
                    );
                }

                $this->executeCommandCollection($commands, $handler)
                    ->otherwise(
                        function ($reason) use ($handler) {
                            $handler = $handler ?? $this->getDefaultExceptionHandler();
                            $handler->handle($reason);
                        }
                    )->wait();
            }

            $this->dispatchEvent(PostLoadEvent::class, $this);

            return $this;
        });
    }

    /**
     * Get the cache key this collection should use.
     *
     * @throws ResponseModelCollectionException
     */
    protected function getCacheKey(): string
    {
        try {
            return static::getCollectionClass()
                . static::getCollectionKey()
                . static::getLoadCommand()
                . static::getMaxResultsToLoad()
                . static::hashArray($this->argsForCacheKey)
                . static::getCountValuePath()
                . static::hashArray(static::getCountArguments());
        } catch (ResponseModelSetupException $e) {
            throw new ResponseModelCollectionException(
                'Could not get cache key for the response model collection!',
                $e
            );
        }
    }

    /**
     * @inheritDoc
     *
     * @throws ResponseModelSetupException
     */
    public static function getMaxResultsToLoad(): int
    {
        static::checkForConstException('LOAD_MAX_RESULTS');

        return static::LOAD_MAX_RESULTS;
    }

    /**
     * Get the dot value path to use when retrieving the value for the size of
     * this collection.
     *
     * @example if our data was ['one' => ['two' => ['size' => 100]]],
     *          this method should return 'one.two.size';
     * @example if our data was ['max-results' => 1, 'size' => 100],
     *          this method should return 'size'
     *
     * @return string
     *
     * @throws ResponseModelSetupException
     */
    public static function getCountValuePath(): string
    {
        static::checkForConstException('COUNT_VALUE_PATH');

        return static::COUNT_VALUE_PATH;
    }

    /**
     * @inheritDoc
     *
     * @throws ResponseModelSetupException
     */
    public static function getCountArguments(): array
    {
        static::checkForConstException('COUNT_ARGUMENTS');

        return static::COUNT_ARGUMENTS;
    }

    /**
     * Attempt to load the collection from cache.
     *
     * The argsForCacheKey property must be set before this method is run!
     *
     * @return bool true if we were able to load from cache, false otherwise
     *
     * @throws ResponseModelCollectionException if the cache status could not
     *                                          be determined
     */
    private function attemptLoadFromCache(): bool
    {
        //Can we exit early by retrieving cache?
        try {
            if ($this->isCached()) {
                $this->getResponseCache();

                //Now that we have our responses from cache, we can allow users
                //to modify them
                /* @var PreLoadFromCacheEvent $event */
                $event = $this->dispatchEvent(
                    PreLoadFromCacheEvent::class,
                    $this,
                    $this->responses
                );

                $this->responses = $event->getResponseData()
                    ?? $this->responses;

                $this->addFromResponses();

                $this->dispatchEvent(
                    PostLoadFromCacheEvent::class,
                    $this,
                    $this->responses
                );

                //No need to keep this around...
                unset($this->responses);

                $this->loaded          = true;
                $this->loadedFromCache = true;

                return true;
            }
        } catch (ResponseModelSetupException $e) {
            throw new ResponseModelCollectionException(
                'Could not determine cache status!',
                $e
            );
        }

        return false;
    }

    /**
     * Get any previous response cache we've saved.
     *
     * @throws ResponseModelSetupException if the settings for this response
     *                                     model collection have not been set
     */
    protected function getResponseCache()
    {
        $this->responses = $this->getCache();
    }

    /**
     * Add data to this response model collection from the responses we've
     * received from the service client.
     *
     * @throws ResponseModelCollectionException
     * @throws ResponseModelSetupException
     */
    private function addFromResponses()
    {
        /** @var PreAddFromResponsesEvent $event */
        $event = $this->dispatchEvent(PreAddFromResponsesEvent::class,
            $this,
            $this->responses
        );

        //The event could have modified the responses
        $this->responses = $event->getResponses();

        foreach ($this->responses as $responseData) {
            $this->add($responseData);
        }

        $this->dispatchEvent(PostAddFromResponsesEvent::class,
            $this,
            $this->responses
        );
    }

    /**
     * Prepare the collection for loading of all items in the collection.
     *
     * This method can accept an optional size argument which forces the size of
     * the collection up front. This is useful if size information has already
     * been determined (e.g. when a list of commands to run asynchronously to
     * build the collection have been generated). If the size data is not
     * provided, the size of the collection is determined by running the
     * collection's COUNT_COMMAND.
     *
     * @param array $commandArgs arguments to send to the count command in
     *                           addition to the COUNT_ARGUMENTS
     * @param int   $size        if known, the size of the collection can
     *                           be provided
     */
    protected function initialize(array $commandArgs = [], int $size = null)
    {
        $this->initializeAsync($commandArgs, $size)->wait();
    }

    /**
     * Perform initialization of the model in an asynchronous fashion.
     *
     * @param array    $commandArgs
     * @param int|null $size
     *
     * @return PromiseInterface
     */
    protected function initializeAsync(
        array $commandArgs = [],
        int $size = null
    ): PromiseInterface {
        return Promise::async(function () use ($commandArgs, $size) {
            //No need to do anything if we're cached.
            if ($this->isCached()) {
                return null;
            }

            $this->dispatchEvent(
                PreCountEvent::class,
                $this
            );

            if (null !== $size) {
                //If we've been provided the size data it's because we already
                //have it.
                $countData = new FulfilledPromise(
                    static::dotToArray(static::getCountValuePath(), $size)
                );
            } else {
                //Create a new Count model which will hold our count data.
                $countData = Count::dataForAsync(
                    $this->getClient(),
                    static::class,
                    $commandArgs
                );
            }

            return $countData;
        })->then(function ($countData) {
            //We'll set the data in this model to include count information.
            //More data will be added later.
            $this->setData($countData);

            $this->dispatchEvent(
                PostCountEvent::class,
                $this,
                $countData
            );
        })->otherwise(function ($reason) {
            throw new ResponseModelCollectionException(
                sprintf(
                    'Count data for %s could not be determined!',
                    static::class
                ),
                $reason
            );
        });
    }

    /**
     * Return a function which will compile arguments necessary to obtain
     * a collection of commands for bulk loading.
     *
     * The callable will receive as its first parameter any
     * load arguments provided for the command. Its second parameter is the
     * current start-index and the third parameter is the maximum number of
     * items each response should contain.
     *
     * It should take the given array of command arguments and add the necessary
     * arguments to load a specific portion of the total items in the collection
     * based on the start-index and max-results.
     *
     * @see ServiceClient::getChunkedCommands
     *
     * @return callable
     *
     * @throws ResponseModelSetupException
     */
    protected function getGenerateCountArgsCallback(): callable
    {
        throw new ResponseModelSetupException('You must override ' . __FUNCTION__ . ' in order to use it!');
    }

    /**
     * Execute all of the commands asynchronously.
     *
     * The returned promise MUST have its `wait` method called at some point to
     * confirm the commands have completed.
     *
     * @param array[CommandInterface] $commands The commands to run
     *
     * @return PromiseInterface a promise which must be waited on in order for
     *                          all the loading to be completed
     */
    protected function executeCommandCollection(
        array $commands,
        ExceptionHandlerInterface $handler = null
    ): PromiseInterface {
        //No need to do anything if we've already loaded data.
        if ($this->loaded) {
            return new FulfilledPromise($this);
        }

        return Promise::async(function () use ($commands, $handler) {
            //Allow others to modify the commands before execution.
            /* @var PreExecuteCommandsEvent $event */
            $event = $this->dispatchEvent(
                PreExecuteCommandsEvent::class,
                $this,
                $commands
            );

            //Use the command from the dispatched event if it's not null
            $commands = $event->getCommands() ?? $commands;

            $this->executeAllCommands($commands, $handler)->wait();

            $this->dispatchEvent(
                PostExecuteCommandsEvent::class,
                $this,
                $commands
            );

            $this->finalizeResponses();

            return $this;
        })->otherwise(function ($reason) {
            throw new ResponseModelCollectionException('Could not load data from command collection!', $reason);
        });
    }

    /**
     * @param Command[] $commands
     *
     * @return \GuzzleHttp\Promise\Promise|PromiseInterface
     */
    private function executeAllCommands(
        array $commands,
        ExceptionHandlerInterface $handler = null
    ) {
        return $this->client->executeAllAsync($commands, [
            //If our responses were received correctly...
            'fulfilled' => function (
                $value,
                $index,
                PromiseInterface $aggregate
            ) use ($commands) {
                //Allow others to modify the commands before execution.
                /** @var CommandFulfilledEvent $event */
                $event = $this->dispatchEvent(
                    CommandFulfilledEvent::class,
                    $this,
                    $commands,
                    $index,
                    $value,
                    $aggregate
                );

                //Use the command from the dispatched event in case it
                //was modified.
                $value = $event->getValue();

                $this->addResponse($value);

                //We're good to resolve now!
                return new FulfilledPromise($value);
            },
            'rejected'  => function ($reason) use ($handler) {
                $handler  = $handler ?? $this->getDefaultExceptionHandler();
                $response = $handler->handle($reason);
                if (null !== $response && is_array($response)) {
                    $this->addResponse($response);
                }
            },
        ]);
    }

    /**
     * Add the given response data to the list of responses we've received.
     */
    private function addResponse(array $data)
    {
        $this->responses[] = $data;
    }

    /**
     * Setup a sane default exception handler for use with our loading method.
     *
     * @return ExceptionHandlerInterface
     */
    protected function getDefaultExceptionHandler(): ExceptionHandlerInterface
    {
        return ResponseModelExceptionHandler::passThruAndWrapWith(
            ResponseModelCollectionException::class,
            ['Could not load response model collection!']
        );
    }

    /**
     * @throws ResponseModelCollectionException
     */
    private function finalizeResponses()
    {
        try {
            //Now that we've got our set of responses, add their data
            //to the collection.
            $this->addFromResponses();

            //Set the response cache so we don't have to request
            //the data again.
            $this->setResponseCache();

            //No need to keep our responses array around...
            unset($this->responses);

            return new FulfilledPromise($this);
        } catch (Exception $e) {
            throw new ResponseModelCollectionException(
                'Could not finalize collection response data!',
                $e
            );
        }
    }

    /**
     * Cache the responses we've received from the service command.
     *
     * @throws ResponseModelSetupException if the settings for this response
     *                                     model collection have not been set
     */
    protected function setResponseCache()
    {
        $this->setCache($this->responses);
    }

    /**
     * @inheritDoc
     *
     * @throws ResponseModelSetupException
     */
    public static function getCountCommand(): string
    {
        static::checkForConstException('COUNT_COMMAND');

        return static::COUNT_COMMAND;
    }

    /**
     * @inheritDoc
     *
     * @throws IncorrectParentResponseModel if the given parent model does not
     *                                      implement ResponseModelInterface
     */
    public function setParent($parent)
    {
        //We make it so collection models can only have regular response models
        static::confirmCorrectParentModel(
            ResponseModelInterface::class,
            $parent
        );

        $this->parent = $parent;

        return $this;
    }

    public function __toString()
    {
        $output    = parent::__toString();
        $output    .= 'Collection:' . PHP_EOL;
        $structure = [
            'Load command'      => static::LOAD_COMMAND,
            'Load arguments'    => static::LOAD_ARGUMENTS,
            'Count command'     => static::COUNT_COMMAND,
            'Count arguments'   => static::COUNT_ARGUMENTS,
            'Collection class'  => static::COLLECTION_CLASS,
            'Size'              => $this->getSize(),
            'Count'             => $this->count(),
            'Load chunks'       => static::getMaxResultsToLoad(),
            'Loaded'            => $this->loaded,
            'Loaded with data'  => $this->loadedWithData,
            'Loaded from cache' => $this->loadedFromCache,
            'Parent'            => get_class($this->parent),
        ];
        $output    .= static::outputStructure($structure);

        return $output;
    }

    /**
     * Return whether or not this collection is empty.
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return 0 === $this->count();
    }

    public function sortBy(string $dotPath)
    {
        usort($this->collection, static function (
            ResponseModelInterface $a,
            ResponseModelInterface $b
        ) use ($dotPath) {
            return $a->dot($dotPath) <=> $b->dot($dotPath);
        });
    }

    public function mapFlat(callable $map, ...$args): array
    {
        return static::flattenArray($this->map($map, ...$args));
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
     * Applies the callback to the child response models in this collection
     *
     * @example To return child data from this collection:
     *
     * //Returns an array of the return values from methodToGetWhatever
     * $this->map(function ($item) {
     *     return $item->methodToGetWhatever();
     * });
     *
     * @see     array_map()
     *
     * @param callable $map a mapping callback to be used with array_map
     * @param array    ...  supplementary variable list of array arguments to
     *                      run through the callback function.
     *
     * @return array
     */
    public function map(callable $map, ...$args): array
    {
        return array_map($map, $this->collection, $args);
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
        array_walk($this->collection, $walk, $userData);

        return $this;
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
     * @return array the aggregate results
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
     * Walk through our collection and return the first item the callback
     * function returns true for.
     *
     * @param callable $accept function passed each item in the collection
     *                         iteratively;
     *
     * @return mixed the first model we accept or false if none are accepted
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
     * @return false|mixed
     */
    protected function filterAndReturnFirst(callable $filter)
    {
        $items = $this->filter($filter);

        return (count($items)) ? array_shift($items) : false;
    }
}
