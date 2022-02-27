<?php

namespace Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection;

use Cob\Bundle\ApiServicesBundle\Models\Events\CanGetCommandArgsTrait;
use Cob\Bundle\ApiServicesBundle\Models\Events\CanSetCommandArgsTrait;
use Cob\Bundle\ApiServicesBundle\Models\ResponseModelCollectionConfig;

class ResponseModelCollectionPreGetLoadCommandEvent extends ResponseModelCollectionEvent
{
    use CanGetCommandArgsTrait;
    use CanSetCommandArgsTrait;

    const NAME = 'api_services.response_model.collection.pre_get_load_command_event';
    /**
     * @var array
     */
    private $commandArgs;

    public function __construct(
        ResponseModelCollectionConfig $config,
        array $commandArgs = []
    ) {
        $this->commandArgs = $commandArgs;

        parent::__construct($config);
    }
}