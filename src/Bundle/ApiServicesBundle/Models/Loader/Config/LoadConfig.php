<?php

namespace Cob\Bundle\ApiServicesBundle\Models\Loader\Config;

use Cob\Bundle\ApiServicesBundle\Models\ExceptionHandlers\ExceptionHandlerInterface;
use Cob\Bundle\ApiServicesBundle\Models\ServiceClientInterface;

class LoadConfig
{
    use LoadConfigSharedTrait;

    public function __construct(
        ServiceClientInterface $client,
        array $commandArgs = null,
        $parent = null,
        bool $clearCache = null,
        ExceptionHandlerInterface $handler = null,
        array $existingData = null,
        $rawData = null
    ) {
        $this->client = $client;
        $this->commandArgs = $commandArgs ?? [];
        $this->parent = $parent;
        $this->clearCache = $clearCache ?? false;
        $this->handler = $handler;
        $this->existingData = $existingData;
        $this->rawData = $rawData;
    }

    public static function builder(string $forClass, ServiceClientInterface $client): LoadConfigBuilder
    {
        return new LoadConfigBuilder($forClass, $client);
    }
}
