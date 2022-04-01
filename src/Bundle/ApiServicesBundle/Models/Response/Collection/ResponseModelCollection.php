<?php

namespace Cob\Bundle\ApiServicesBundle\Models\Response\Collection;

use Cob\Bundle\ApiServicesBundle\Models\Config\ResponseModelCollectionConfig;
use Cob\Bundle\ApiServicesBundle\Models\Loader\Config\CollectionLoadConfig;
use Cob\Bundle\ApiServicesBundle\Models\Loader\LoadState;
use Cob\Bundle\ApiServicesBundle\Models\Response\HasParent;
use Cob\Bundle\ApiServicesBundle\Models\ServiceClientInterface;
use Cob\Bundle\ApiServicesBundle\Models\UsesDot;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Selectable;
use GuzzleHttp\Promise\PromiseInterface;

interface ResponseModelCollection extends Collection, Selectable, UsesDot, HasParent
{
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
    );

    public function getClient(): ServiceClientInterface;

    /**
     * Get the configuration of this response model collection used to establish default behavior.
     *
     * @return ResponseModelCollectionConfig the config for this response model collection
     */
    public static function getConfig(): ResponseModelCollectionConfig;

    /**
     * Obtain a new response model collection after loading its data asynchronously.
     *
     * @param CollectionLoadConfig $loadConfig the load-time configuration to be used.
     *
     * @return ResponseModelCollection the collection whose data will be loaded upon first attempt at obtaining data.
     */
    public static function loadAsync(CollectionLoadConfig $loadConfig): ResponseModelCollection;

    /**
     * Obtain a new response model collection whose data will be loaded synchronously.
     *
     * @param CollectionLoadConfig $loadConfig the load-time configuration to be used.
     *
     * @return ResponseModelCollection the collection whose data has been loaded synchronously.
     */
    public static function load(CollectionLoadConfig $loadConfig): ResponseModelCollection;

    /**
     * Obtain a new response model collection whose data is based on existing data.
     *
     * @param CollectionLoadConfig $loadConfig the load-time configuration to be used.
     *
     * @return ResponseModelCollection the collection whose data is based on existing data.
     */
    public static function withData(CollectionLoadConfig $loadConfig): ResponseModelCollection;

    public function toArray(): array;

    public function isWaiting(): bool;
    public function isLoaded(): bool;
    public function isLoadedWithData(): bool;
}
