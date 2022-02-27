<?php

namespace Cob\Bundle\ApiServicesBundle\Models\Events;

use GuzzleHttp\Command\CommandInterface;

trait CanGetCommandTrait
{
    use HoldsCommandTrait;

    public function getCommand(): CommandInterface
    {
        return $this->command;
    }
}