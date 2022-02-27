<?php

namespace Cob\Bundle\ApiServicesBundle\Models\Events;

use GuzzleHttp\Command\CommandInterface;

trait HoldsCommandTrait
{
    /**
     * @var CommandInterface
     */
    private $command;
}