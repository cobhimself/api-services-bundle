<?php

namespace Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel;

use Cob\Bundle\ApiServicesBundle\Models\Config\ResponseModelConfig;

class ResponseModelPreGetLoadCommandEvent extends ResponseModelEvent
{
    const NAME = 'api_services.response_model.pre_get_load_command_event';
    /**
     * @var array
     */
    private $commandArgs;

    public function __construct(
        ResponseModelConfig $config,
        array $commandArgs = []
    ) {
        $this->commandArgs = $commandArgs;

        parent::__construct($config);
    }

    /**
     * @return array
     */
    public function getCommandArgs(): array
    {
        return $this->commandArgs;
    }

    /**
     * @param array $commandArgs
     */
    public function setCommandArgs(array $commandArgs)
    {
        $this->commandArgs = $commandArgs;
    }
}
