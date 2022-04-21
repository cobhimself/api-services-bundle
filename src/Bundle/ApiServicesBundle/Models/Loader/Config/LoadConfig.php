<?php

namespace Cob\Bundle\ApiServicesBundle\Models\Loader\Config;

use Cob\Bundle\ApiServicesBundle\Models\ExceptionHandlers\ExceptionHandlerInterface;
use Cob\Bundle\ApiServicesBundle\Models\ServiceClientInterface;
use Cob\Bundle\ApiServicesBundle\Models\Util\LogUtil;

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

    /**
     * String representation of this load configuration.
     *
     * @return string
     */
    public function __toString(): string
    {
        return 'Load Config (' . get_class($this) . '):' . PHP_EOL .
            LogUtil::outputStructure([
                'Command Args'      => json_encode($this->commandArgs),
                'Parent'            => $this->parent ? get_class($this->parent) : 'none',
                'Clear Cache'       => $this->clearCache,
                'Exception Handler' => $this->handler ? get_class($this->handler) : 'none',
                'Raw Data'          => $this->rawData ? PHP_EOL . PHP_EOL . $this->rawData . PHP_EOL : 'false',
                'Existing Data'     => $this->existingData
                    ? PHP_EOL . PHP_EOL . json_encode($this->existingData) . PHP_EOL
                    : 'false',
            ]);
    }
}
