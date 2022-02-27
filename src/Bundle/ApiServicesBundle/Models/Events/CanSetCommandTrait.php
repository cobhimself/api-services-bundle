<?php

namespace Cob\Bundle\ApiServicesBundle\Models\Events;

use GuzzleHttp\Command\CommandInterface;

trait CanSetCommandTrait
{
    use HoldsCommandTrait;

    public function setCommand(CommandInterface $command)
    {
        $this->command = $command;
    }
}