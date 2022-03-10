<?php

namespace Cob\Bundle\ApiServicesBundle\Models\Loader\Config;

use Cob\Bundle\ApiServicesBundle\Models\ExceptionHandlers\ExceptionHandlerInterface;
use Cob\Bundle\ApiServicesBundle\Models\ServiceClientInterface;

class CollectionLoadConfig
{
    use LoadConfigSharedTrait;

    /**
     * @var array|mixed
     */
    private $countCommandArgs;

    public function __construct(
        ServiceClientInterface $client,
        array $commandArgs = null,
        array $countCommandArgs = null,
        $parent = null,
        bool $clearCache = null,
        ExceptionHandlerInterface $handler = null,
        array $existingData = null
    ) {
        $this->client = $client;
        $this->commandArgs = $commandArgs ?? [];
        $this->countCommandArgs = $countCommandArgs ?? [];
        $this->parent = $parent;
        $this->clearCache = $clearCache ?? false;
        $this->handler = $handler;
        $this->existingData = $existingData;
        $this->client = $client;
    }

    /**
     * @return array|mixed
     */
    public function getCountCommandArgs()
    {
        return $this->countCommandArgs;
    }

    public static function builder(
        string $forClass,
        ServiceClientInterface $client
    ): CollectionLoadConfigBuilder {
        return new CollectionLoadConfigBuilder($forClass, $client);
    }
}