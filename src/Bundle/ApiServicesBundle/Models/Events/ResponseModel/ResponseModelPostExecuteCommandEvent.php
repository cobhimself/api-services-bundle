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
use Cob\Bundle\ApiServicesBundle\Models\ResponseModelInterface;

/**
 * Run after a command produces results.
 *
 * @see AbstractResponseModel::load()
 */
class ResponseModelPostExecuteCommandEvent extends ResponseModelEvent
{
    const NAME = 'api_services.response_model.post_execute_command';

    /**
     * The command this event is associated with.
     *
     * @var CommandInterface
     */
    protected $command;

    /**
     * @var mixed The response data from the command's run
     */
    protected $response;

    /**
     * @param ResponseModelInterface $model    the response model
     * @param CommandInterface       $command  the command that was run
     * @param mixed                  $response the response returned from
     *                                         the command
     */
    public function __construct(
        ResponseModelInterface $model,
        CommandInterface $command,
        $response
    ) {
        $this->command = $command;
        $this->response = $response;
        parent::__construct($model);
    }

    /**
     * Get the command this event is associated with.
     */
    public function getCommand(): CommandInterface
    {
        return $this->command;
    }

    /**
     * Get the response data which was just loaded.
     */
    public function getResponse()
    {
        return $this->response;
    }
}
