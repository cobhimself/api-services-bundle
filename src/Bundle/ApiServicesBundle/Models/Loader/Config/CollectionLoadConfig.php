<?php

namespace Cob\Bundle\ApiServicesBundle\Models\Loader\Config;

use Cob\Bundle\ApiServicesBundle\Models\ExceptionHandlers\ExceptionHandlerInterface;
use Cob\Bundle\ApiServicesBundle\Models\ServiceClientInterface;
use Cob\Bundle\ApiServicesBundle\Models\Util\LogUtil;
use Cob\Bundle\ApiServicesBundle\Models\Util\ObjectDetailsBuilder;

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

    public function __toString(): string
    {
        return 'Collection Load Config (' . get_class($this) . '):' . PHP_EOL .
            LogUtil::outputStructure([
                'Command Args'       => json_encode($this->commandArgs),
                'Count Command Args' => json_encode($this->countCommandArgs),
                'Parent'             => $this->parent ? get_class($this->parent) : 'none',
                'Clear Cache'        => $this->clearCache,
                'Exception Handler'  => $this->handler ? get_class($this->handler) : 'none',
                'Existing Data'      => $this->existingData
                    ? PHP_EOL . PHP_EOL . json_encode($this->existingData) . PHP_EOL
                    : 'false',
            ]);
    }
}
