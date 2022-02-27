<?php

namespace Cob\Bundle\ApiServicesBundle\Models\Events;

trait CanSetHashTrait
{
    use HoldsHashTrait;

    public function setHash(string $hash)
    {
        $this->hash = $hash;
    }
}