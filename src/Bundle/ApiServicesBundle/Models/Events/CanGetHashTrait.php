<?php

namespace Cob\Bundle\ApiServicesBundle\Models\Events;

trait CanGetHashTrait
{
    use HoldsHashTrait;

    public function getHash(): string
    {
        return $this->hash;
    }
}