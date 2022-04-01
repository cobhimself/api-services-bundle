<?php

namespace Cob\Bundle\ApiServicesBundle\Models\Response;

use Cob\Bundle\ApiServicesBundle\Models\Config\ResponseModelConfig;
use Cob\Bundle\ApiServicesBundle\Models\Loader\Config\LoadConfig;
use Cob\Bundle\ApiServicesBundle\Models\Loader\LoadState;
use Cob\Bundle\ApiServicesBundle\Models\ServiceClientInterface;
use Cob\Bundle\ApiServicesBundle\Models\UsesDot;
use GuzzleHttp\Promise\PromiseInterface;

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

    public function getClient(): ServiceClientInterface;

    public static function loadAsync(LoadConfig $loadConfig): ResponseModel;
    public static function load(LoadConfig $loadConfig): ResponseModel;
    public static function withData(LoadConfig $loadConfig): ResponseModel;
    public static function withRawData(LoadConfig $loadConfig): ResponseModel;

    public function isWaiting(): bool;
    public function isLoaded(): bool;
    public function isLoadedWithData(): bool;

    public function toArray(): array;

    public function getRawData();

    public static function getConfig(): ResponseModelConfig;
}
