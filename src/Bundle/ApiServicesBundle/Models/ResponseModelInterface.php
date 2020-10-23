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

use GuzzleHttp\Command\ResultInterface;
use GuzzleHttp\Promise\PromiseInterface;
use Cob\Bundle\ApiServicesBundle\Exceptions\ResponseModelSetupException;
use Cob\Bundle\ApiServicesBundle\Models\ExceptionHandlers\ExceptionHandlerInterface;

interface ResponseModelInterface
{
    /**
     * Add a callback to be run upon response model initialization.
     *
     * Called after data is set in the model.
     *
     * @param callable $initCallback
     */
    public function addInitCallback(callable $initCallback);

    /**
     * Helper method which aids in the traversal of data within the
     * response model.
     *
     * If a dot path has been resolved before, the value is returned without
     * having to traverse the data structure thanks to caching.
     *
     * @example For the given array structure:
     *
     * $data = [
     *    'one' => 1,
     *    'parent' => [
     *        'child1' => [
     *             'child2' => true
     *        ]
     *    ]
     * ];
     *
     * $this->dot('one'); //1
     * $this->dot('parent'); //['child1' => ['child2' => true]]
     * $this->dot('parent.child1'); //['child2' => true]
     * $this->dot('parent.child1.child2'); //true
     * $this->dot('parent.child1.child3'); //false
     * #this->dot('parent.child1.child3', 'my_default'); //'my_default'
     *
     * @param string                     $key     the key to use as the path to
     *                                            find the data
     * @param false|mixed                $default if the key path cannot be
     *                                            found, or if the key is empty,
     *                                            return this value
     * @param array|ResultInterface|null $data    When null, the data traversed
     *                                            is the response model's data.
     *                                            However, if provided, the data
     *                                            is traversed and the data at
     *                                            the key path is returned (or
     *                                            default if not found); no
     *                                            caching is done. Caching is
     *                                            only done for the original
     *                                            full key path when this method
     *                                            is called recursively.
     *
     * @return false|mixed By default, if the data cannot be found, false is
     *                     returned. Otherwise, if a default value has been
     *                     provided, the default will be returned in that case.
     *                     If data is found at the key path, the data found
     *                     is returned.
     */
    public function dot(string $key, $default = false, $data = null);

    /**
     * Get the service client for this response model.
     */
    public function getClient(): ServiceClient;

    /**
     * Get the data associated with this response model.
     */
    public function getData(): array;

    /**
     * Load data for this response model from its service command.
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
     * Set the service client for this response model to use.
     *
     * @return ResponseModelInterface|ResponseModelCollectionInterface
     */
    public function setClient(ServiceClient $client);

    /**
     * Set data for this response model.
     *
     * @param mixed  the data to set
     * @param string if provided, the data is set at the given key within the
     *               model's data; otherwise, the given data is set as the
     *               model's data
     *
     * @return ResponseModelInterface|ResponseModelCollectionInterface
     */
    public function setData($data, string $key = null);

    /**
     * Trigger all callbacks registered to be run before the loading of this
     * response model's data from a service.
     *
     * @param ResponseModelInterface $model the model to be sent to each of
     *                                      the registered callbacks
     */
    public function triggerCallbacks(ResponseModelInterface $model);

    /**
     * Return the arguments to use with the service command which loads
     * this response model.
     *
     * @throws ResponseModelSetupException
     */
    public static function getLoadArguments(): array;

    /**
     * @return string the service command used to load the data for
     *                this response model
     */
    public static function getLoadCommand(): string;

    /**
     * Return the model with data loaded from the service client.
     *
     * @param ServiceClient $client                                           the service client to use to load the data
     * @param array $loadArguments                                            the load arguments to send the model's
     *                                                                        load command
     * @param ResponseModelCollectionInterface|ResponseModelInterface $parent the parent model associated with this
     *                                                                        response model
     * @param callable|null $initCallback                                     a callback to call before the response
     *                                                                        model is loaded
     * @param ExceptionHandlerInterface|null $handler
     * @param bool $clearCache                                                whether or not to clear the cache
     *                                                                        before loading
     *
     * @return ResponseModelInterface
     */
    public static function getLoaded(
        ServiceClient $client,
        array $loadArguments = [],
        $parent = null,
        callable $initCallback = null,
        ExceptionHandlerInterface $handler = null,
        bool $clearCache = false
): ResponseModelInterface;

    /**
     * Return the response model with existing data.
     *
     * @param ServiceClient                                           $client the service client to use when
     *                                                                        retrieving data
     * @param array                                                   $data
     * @param ResponseModelCollectionInterface|ResponseModelInterface $parent the parent model associated with this
     *                                                                        response model
     *
     * @return ResponseModelInterface|ResponseModelCollectionInterface
     */
    public static function withData(
        ServiceClient $client,
        array $data = [],
        $parent = null
    );

    /**
     * Get data for the Response Model utilizing a promise which MUST be
     * fulfilled for the data to be loaded.
     *
     * @param ServiceClient                                           $client        the service client which will do
     *                                                                               the loading
     * @param PromiseInterface|null                                   $promise       a variable sent in by reference and
     *                                                                               which will be filled with the
     *                                                                               promise necessary to load the
     *                                                                               response model
     * @param ResponseModelCollectionInterface|ResponseModelInterface $parent        the parent associated with this
     *                                                                               response model
     * @param array                                                   $loadArguments the arguments we will send the
     *                                                                               Response Model command
     * @param callable|null                                           $initCallback  a callback which will be run upon
     *                                                                               Response Model data being loaded
     * @param ExceptionHandlerInterface|null                          $handler       the exception handler we will use
     *                                                                               when we load our Response Model
     *
     * @return ResponseModelInterface|ResponseModelCollectionInterface
     */
    public static function getUsingPromise(
        ServiceClient $client,
        PromiseInterface &$promise = null,
        $parent = null,
        array $loadArguments = [],
        callable $initCallback = null,
        ExceptionHandlerInterface $handler = null,
        bool $clearCache = false
    );

    /**
     * Set the raw data for the model.
     *
     * This is only useful for models which do not have structured data to work
     * with and the raw data is the model's data.
     *
     * @param $data
     */
    public function setRawData($data): ResponseModelInterface;

    /**
     * Obtain the raw data associated with this model.
     *
     * @return false|mixed
     */
    public function getRawData();

    /**
     * Associate this model with a parent model or collection.
     *
     * @param responseModelInterface|ResponseModelCollectionInterface $parent
     *                                                                        The parent model or collection this model/collection belongs to
     *
     * @return ResponseModelInterface|ResponseModelCollectionInterface|null
     */
    public function setParent($parent);
}
