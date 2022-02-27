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

use Cob\Bundle\ApiServicesBundle\Models\ResponseModelCollectionConfig;
use GuzzleHttp\Command\CommandInterface;
use GuzzleHttp\Promise\Promise;

/**
 * Event triggered right before a ResponseModelCollectionInterface instance
 * has a single command within a group of commands fulfilled.
 */
class CommandFulfilledEvent extends ResponseModelCollectionEvent
{
    const NAME = 'api_services.response_model.collection.command_fulfilled';

    /**
     * @var CommandInterface[] An array of commands being run
     */
    protected $commands;

    /**
     * @var int the index of the command being run among all the other commands
     */
    private $index;

    /**
     * @var mixed the value returned by the current command
     */
    private $value;

    /**
     * @var Promise the promise which wraps the set of command promises
     */
    private $aggregate;

    /**
     * Run after a command within our chunked commands list
     * completes successfully.
     *
     * @see AbstractResponseModelCollection::executeCommandCollection()
     *
     * @param CommandInterface[] $commands  full array of commands being run
     * @param int                $index     index of fulfilled command
     * @param mixed              $value     the command return value
     * @param Promise            $aggregate the aggregate group of promises
     *                                      being run
     */
    public function __construct(
        ResponseModelCollectionConfig $config,
        array $commands,
        int $index,
        $value,
        Promise $aggregate
    ) {
        $this->commands  = $commands;
        $this->index     = $index;
        $this->value     = $value;
        $this->aggregate = $aggregate;

        parent::__construct($config);
    }

    /**
     * @return CommandInterface[] the group of commands being executed
     */
    public function getCommands(): array
    {
        return $this->commands;
    }

    /**
     * @return int the index of the command which was fulfilled
     */
    public function getIndex(): int
    {
        return $this->index;
    }

    /**
     * @return mixed the value returned by the command
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return Promise the promise containing all of the command promises
     */
    public function getAggregate(): Promise
    {
        return $this->aggregate;
    }
}
