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

use GuzzleHttp\Promise\PromiseInterface;
use Cob\Bundle\ApiServicesBundle\Exceptions\ResponseModelSetupException;
use Cob\Bundle\ApiServicesBundle\Models\ExceptionHandlers\ExceptionHandlerInterface;

interface ResponseModelCollectionInterface extends ResponseModelInterface
{
    /**
     * Add a callback to be run for each model in the collection
     * upon initialization.
     *
     * @param callable $initCallback
     */
    public function addInitCallback(callable $initCallback);

    /**
     * Trigger all callbacks registered to be run for each model in the
     * collection upon initialization.
     *
     * @param ResponseModelInterface $model the model to be sent to each of the
     *                                      registered callbacks
     */
    public function triggerCallbacks(ResponseModelInterface $model);

    /**
     * Set the service client to use with the response model collection.
     *
     * @return ResponseModelCollectionInterface
     * @noinspection ReturnTypeCanBeDeclaredInspection
     */
    public function setClient(ServiceClient $client);

    /**
     * Get the service client associated with this response model collection.
     */
    public function getClient(): ServiceClient;

    /**
     * Add the given response data to our response model collection.
     *
     * Can be called multiple times to extend the list of items in
     * the collection.
     *
     * @param array $responseData
     */
    public function add(array $responseData = null);

    /**
     * Get the size of the collection as reported by response data.
     */
    public function getSize(): int;

    /**
     * Return all of the items in the collection.
     */
    public function all(): array;

    /**
     * Get the command to use with the service client to load this response
     * model collection with data.
     */
    public static function getLoadCommand(): string;

    /**
     * Get the service command to use to retrieve count information.
     *
     * @return string
     */
    public static function getCountCommand(): string;

    /**
     * Get the number of child models to load in this collection per service
     * command request.
     */
    public static function getMaxResultsToLoad(): int;

    /**
     * Return an empty collection.
     *
     * @param ResponseModelInterface|null $parentModel
     * @param ServiceClient|null          $client
     *
     * @return ResponseModelCollectionInterface
     */
    public static function empty(
        ResponseModelInterface $parentModel = null,
        ServiceClient $client = null
    ): ResponseModelCollectionInterface;

    /**
     * Set data for this response model.
     *
     * @param mixed  the data to set
     * @param string if provided, the data is set at the given key within the
     *               collection's data; otherwise, the given data is set as the
     *               entire collection's data
     *
     * @return ResponseModelCollectionInterface
     * @noinspection ReturnTypeCanBeDeclaredInspection
     */
    public function setData(
        $data,
        string $key = null
    );

    /**
     * Get the data associated with this response model collection.
     */
    public function getData(): array;

    /**
     * Load the data for the collection of models.
     *
     * @param array                          $commandArgs arguments to be used
     *                                                    with the response
     *                                                    model's load command
     * @param ExceptionHandlerInterface|null $handler     handles any
     *                                                    CommandClientException
     * @param bool                           $clearCache  whether or not to
     *                                                    clear the cache before
     *                                                    loading
     */
    public function load(
        array $commandArgs = [],
        ExceptionHandlerInterface $handler = null,
        bool $clearCache = false
    ): PromiseInterface;

    /**
     * Get the arguments to use with the service command for this response model
     * which will return count data.
     *
     * @return array
     */
    public static function getCountArguments(): array;

    /**
     * Get the key to use when retrieving the set of data representing this
     * responses' collection.
     *
     * @return string
     *
     * @throws ResponseModelSetupException
     */
    public static function getCollectionKey(): string;

    /**
     * Get the key to use when retrieving the size of the response
     * model's collection.
     *
     * @return string
     *
     * @throws ResponseModelSetupException
     */
    public static function getCountValuePath(): string;

    /**
     * Get the key to use when retrieving the size of the response
     * model's collection.
     *
     * @return string
     *
     * @throws ResponseModelSetupException
     */
    public static function getCollectionClass(): string;

    /**
     * Factory method used to return an instance of a response model collection
     * using data from its parent model.
     *
     * This is most useful when a model's parent already has the data needed to
     * initialize the collection.
     *
     * @param ResponseModelInterface $parentModel  the parent model this
     *                                             collection belongs to
     * @param string                 $dataKey      The data key in dot notation
     *                                             where the data for this
     *                                             collection can be obtained.
     *                                             (@see ResponseModelInterface::dot())
     * @param callable|null          $initCallback a callback to be added to the
     *                                             initialization stack for each
     *                                             of the models within
     *                                             the collection
     * @param bool                   $requireData  if true, throw an exception
     *                                             if the data we expect to be
     *                                             there isn't
     */
    public static function withDataFromParent(
        ResponseModelInterface $parentModel,
        string $dataKey,
        callable $initCallback = null,
        bool $requireData = false
    ): ResponseModelCollectionInterface;

    /**
     * Associate this response model collection to a parent response model
     *
     * To see how this can be done automatically for you
     *
     * @see ResponseModelCollectionInterface::withDataFromParent()
     *
     * @param ResponseModelInterface|ResponseModelCollectionInterface $parent the parent response model
     *
     * @return ResponseModelCollectionInterface this response model collection
     *
     * @noinspection ReturnTypeCanBeDeclaredInspection
     */
    public function setParent($parent);

    /**
     * Reduce the items in this collection based on whether or not the given
     * filter says the items should stay or go.
     *
     * @param callable $filter a callable which, when given an item from within
     *                         this collection, returns true to keep the item
     *                         or false to remove it
     *
     * @return ResponseModelCollectionInterface collection
     *                                          this collection after it's collection property has been reduced
     *                                          to only have items which make it through the filter
     */
    public function reduce(callable $filter): ResponseModelCollectionInterface;

    /**
     * Return a subset of this collection based on the given filter callback.
     *
     * The callable should return true for the item to be included, false for
     * it to not be included.
     *
     * @param callable $filter a callable which accepts a single item from this
     *                         collection and determines whether or not it
     *                         should be included in the final list of items
     *
     * @return array an array of items from this collection which made it
     *               through the filter
     */
    public function filter(callable $filter): array;

    /**
     * Clear the collection completely.
     *
     * @return ResponseModelCollectionInterface
     */
    public function clear(): ResponseModelCollectionInterface;
}
