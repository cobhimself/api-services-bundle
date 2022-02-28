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

use Cob\Bundle\ApiServicesBundle\Models\Events\CanGetResponseTrait;
use Cob\Bundle\ApiServicesBundle\Models\Events\CanSetResponseTrait;
use Cob\Bundle\ApiServicesBundle\Models\ResponseModelCollectionConfig;
use GuzzleHttp\Command\CommandInterface;

/**
 * Run after a group of commands are run to populate a
 * ResponseModelCollectionInterface instance.
 */
class PostExecuteCommandsEvent extends ResponseModelCollectionEvent
{
    use CanGetResponseTrait;
    use CanSetResponseTrait;

    const NAME = 'api_services.response_model.collection.post_execute_commands';

    /**
     * @var CommandInterface[] an array of commands which were just executed
     */
    protected $commands;

    /**
     * Run after a chunked set of commands are run for the collection.
     *
     * @param CommandInterface[] $commands
     */
    public function __construct(
        ResponseModelCollectionConfig $config,
        array $commands,
        array $combinedResponse = []
    ) {
        $this->response = $combinedResponse;
        $this->commands = $commands;
        parent::__construct($config);
    }

    /**
     * Get the commands which were just run.
     *
     * @return CommandInterface[]
     */
    public function getCommands(): array
    {
        return $this->commands;
    }
}
