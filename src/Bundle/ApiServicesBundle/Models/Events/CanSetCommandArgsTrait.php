<?php

namespace Cob\Bundle\ApiServicesBundle\Models\Events;

trait CanSetCommandArgsTrait
{
    use HoldsCommandArgsTrait;

    public function setCommandArgs(array $commandArgs)
    {
        $this->commandArgs = $commandArgs;
    }
}