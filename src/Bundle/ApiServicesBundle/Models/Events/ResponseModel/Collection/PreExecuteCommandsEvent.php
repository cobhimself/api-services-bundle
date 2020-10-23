<?php
/*
 * This file is part of the cobhimself/api-services-bundle package.
 *
 * (c) Collin D. Brooks <collin.brooks@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection;

use Cob\Bundle\ApiServicesBundle\Models\ResponseModelCollectionInterface;
use GuzzleHttp\Command\CommandInterface;

/**
 * Run before a group of commands are run to populate a
 * ResponseModelCollectionInterface instance.
 */
class PreExecuteCommandsEvent extends Event
{
    const NAME = 'api_services.response_model.collection.pre_execute_commands';

    /**
     * @var CommandInterface[] an array of commands about to be executed
     */
    protected $commands;

    /**
     * Run before a chunked set of commands are run for the collection.
     *
     * @param CommandInterface[] $commands
     */
    public function __construct(
        ResponseModelCollectionInterface $model,
        array $commands
    ) {
        $this->commands = $commands;
        parent::__construct($model);
    }

    /**
     * Get the commands which are about to be run.
     *
     * @return CommandInterface[]
     */
    public function getCommands(): array
    {
        return $this->commands;
    }
}
