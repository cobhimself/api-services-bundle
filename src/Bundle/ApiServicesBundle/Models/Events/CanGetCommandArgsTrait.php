<?php

namespace Cob\Bundle\ApiServicesBundle\Models\Events;

trait CanGetCommandArgsTrait
{
    use HoldsCommandArgsTrait;

    public function getCommandArgs(): array
    {
        return $this->commandArgs;
    }
}