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

use Cob\Bundle\ApiServicesBundle\Models\ResponseModelCollection;
use Cob\Bundle\ApiServicesBundle\Models\ResponseModelCollectionConfig;
use GuzzleHttp\Command\CommandInterface;

/**
 * Run before a group of commands are run to populate a
 * ResponseModelCollectionInterface instance.
 */
class PreExecuteCommandsEvent extends ResponseModelCollectionEvent
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
        ResponseModelCollectionConfig $config,
        array $commands
    ) {
        $this->commands = $commands;
        parent::__construct($config);
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
