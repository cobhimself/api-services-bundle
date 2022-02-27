<?php

namespace Cob\Bundle\ApiServicesBundle\Models\Events;

trait CanGetResponseTrait
{
    use HoldsResponseTrait;

    public function getResponse(): array
    {
        return $this->response ?? [];
    }
}