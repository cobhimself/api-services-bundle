<?php

namespace Cob\Bundle\ApiServicesBundle\Models\Loader\Config;

use Cob\Bundle\ApiServicesBundle\Models\ServiceClientInterface;

class LoadConfig
{
    use LoadConfigSharedTrait;

    public function __construct(
        ServiceClientInterface $client,
        $commandArgs = [],
        $parent = null,
        $clearCache = false,
        $handler = null,
        $existingData = []
    ) {
        $this->client = $client;
        $this->commandArgs = $commandArgs;
        $this->parent = $parent;
        $this->clearCache = $clearCache;
        $this->handler = $handler;
        $this->existingData = $existingData;
    }

    public static function builder(string $forClass, ServiceClientInterface $client): LoadConfigBuilder
    {
        return new LoadConfigBuilder($forClass, $client);
    }
}