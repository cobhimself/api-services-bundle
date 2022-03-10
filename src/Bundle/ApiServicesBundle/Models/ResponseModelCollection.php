<?php

namespace Cob\Bundle\ApiServicesBundle\Models;

use Cob\Bundle\ApiServicesBundle\Models\Loader\Config\CollectionLoadConfig;
use Cob\Bundle\ApiServicesBundle\Models\Loader\LoadState;
use GuzzleHttp\Promise\PromiseInterface;

interface ResponseModelCollection extends UsesDot, HasParent
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

    public static function loadAsync(CollectionLoadConfig $loadConfig);

    public static function load(CollectionLoadConfig $loadConfig);

    public static function withData(CollectionLoadConfig $loadConfig);

    public function toArray(): array;
}