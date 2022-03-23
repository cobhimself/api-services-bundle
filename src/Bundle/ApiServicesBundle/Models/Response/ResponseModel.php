<?php

namespace Cob\Bundle\ApiServicesBundle\Models\Response;

use Cob\Bundle\ApiServicesBundle\Models\Loader\Config\LoadConfig;
use Cob\Bundle\ApiServicesBundle\Models\Loader\LoadState;
use Cob\Bundle\ApiServicesBundle\Models\ServiceClientInterface;
use GuzzleHttp\Promise\PromiseInterface;
use Cob\Bundle\ApiServicesBundle\Models\UsesDot;

interface ResponseModel extends UsesDot, HasParent
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

    public static function loadAsync(LoadConfig $loadConfig);

    public static function load(LoadConfig $loadConfig);

    public static function withData(LoadConfig $loadConfig);

    public static function withRawData(LoadConfig $loadConfig);

    public function toArray(): array;
}
