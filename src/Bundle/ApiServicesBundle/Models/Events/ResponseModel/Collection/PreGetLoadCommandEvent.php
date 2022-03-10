<?php

namespace Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection;

use Cob\Bundle\ApiServicesBundle\Models\Events\CanGetCollectionLoadConfigTrait;
use Cob\Bundle\ApiServicesBundle\Models\Events\CanSetCollectionLoadConfigTrait;
use Cob\Bundle\ApiServicesBundle\Models\Loader\Config\CollectionLoadConfig;
use Cob\Bundle\ApiServicesBundle\Models\ResponseModelCollectionConfig;

class PreGetLoadCommandEvent extends ResponseModelCollectionEvent
{
    use CanGetCollectionLoadConfigTrait;
    use CanSetCollectionLoadConfigTrait;

    const NAME = 'api_services.response_model.collection.pre_get_load_command_event';

    public function __construct(
        ResponseModelCollectionConfig $config,
        CollectionLoadConfig $loadConfig
    ) {
        $this->setCollectionLoadConfig($loadConfig);

        parent::__construct($config);
    }
}