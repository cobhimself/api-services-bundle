<?php
/*
 * This file is part of the cobhimself/api-services-bundle package.
 *
 * (c) Collin D. Brooks <collin.brooks@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel;

use Cob\Bundle\ApiServicesBundle\Models\AbstractResponseModel;
use Cob\Bundle\ApiServicesBundle\Models\Config\ResponseModelConfig;
use Cob\Bundle\ApiServicesBundle\Models\ResponseModelInterface;
use GuzzleHttp\Command\CommandInterface;

/**
 * Run before a response model command is run.
 *
 * @see AbstractResponseModel::load()
 */
class ResponseModelPreExecuteCommandEvent extends ResponseModelEvent
{
    const NAME = 'api_services.response_model.pre_execute_command';

    /**
     * @var CommandInterface
     */
    protected $command;

    public function __construct(
        ResponseModelConfig $config,
        CommandInterface $command
    ) {
        $this->command = $command;
        parent::__construct($config);
    }

    /**
     * Get the command about to be run.
     *
     * @return CommandInterface
     */
    public function getCommand(): CommandInterface
    {
        return $this->command;
    }
}
