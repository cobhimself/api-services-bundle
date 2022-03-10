<?php

namespace Cob\Bundle\ApiServicesBundle\Models\Loader\Config;

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
        $commandArgs = [],
        $countCommandArgs = [],
        $parent = null,
        $clearCache = false,
        $handler = null,
        $existingData = null
    ) {
        $this->client = $client;
        $this->commandArgs = $commandArgs;
        $this->parent = $parent;
        $this->clearCache = $clearCache;
        $this->handler = $handler;
        $this->existingData = $existingData;

        $this->countCommandArgs = $countCommandArgs;
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