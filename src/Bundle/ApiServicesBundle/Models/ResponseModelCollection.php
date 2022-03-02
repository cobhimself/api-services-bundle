<?php

namespace Cob\Bundle\ApiServicesBundle\Models;

use Cob\Bundle\ApiServicesBundle\Models\Loader\State\LoadState;
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
     */
    public function __construct(
        ServiceClientInterface $client,
        LoadState $desiredLoadState,
        PromiseInterface $loadPromise,
        $parent = null
    );

    public static function loadAsync(
        ServiceClientInterface $client,
        array $commandArgs = [],
        array $countCommandArgs = [],
        $parent = null
    );

    public static function load(
        ServiceClientInterface $client,
        array $commandArgs = [],
        array $countCommandArgs = [],
        $parent = null
    );

    public static function withData(
        ServiceClientInterface $client,
        array $data = [],
        $parent = null
    );

    public function toArray(): array;
}