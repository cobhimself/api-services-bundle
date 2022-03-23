<?php

namespace Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection;

use Cob\Bundle\ApiServicesBundle\Models\Config\ResponseModelCollectionConfig;
use Cob\Bundle\ApiServicesBundle\Models\Events\CanGetCommandTrait;
use Cob\Bundle\ApiServicesBundle\Models\Events\CanSetCommandTrait;
use GuzzleHttp\Command\CommandInterface;

class PreExecuteCommandEvent extends ResponseModelCollectionEvent
{
    use CanGetCommandTrait;
    use CanSetCommandTrait;

    const NAME = 'api_services.response_model.collection.pre_execute_command';

    public function __construct(ResponseModelCollectionConfig $config, CommandInterface $command)
    {
        parent::__construct($config);
        $this->command = $command;
    }
}
