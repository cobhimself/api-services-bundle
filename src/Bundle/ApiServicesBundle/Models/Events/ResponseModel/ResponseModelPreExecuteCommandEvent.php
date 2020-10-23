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

use GuzzleHttp\Command\CommandInterface;
use Cob\Bundle\ApiServicesBundle\Models\AbstractResponseModel;
use Cob\Bundle\ApiServicesBundle\Models\ResponseModelInterface;

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

    /**
     * @param ResponseModelInterface $model   the model whose command is about
     *                                        to be executed
     * @param CommandInterface       $command the command which is about to be
     *                                        executed
     */
    public function __construct(
        ResponseModelInterface $model,
        CommandInterface $command
    ) {
        $this->command = $command;
        parent::__construct($model);
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
