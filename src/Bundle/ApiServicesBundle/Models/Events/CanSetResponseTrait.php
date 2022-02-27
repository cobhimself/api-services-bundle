<?php

namespace Cob\Bundle\ApiServicesBundle\Models\Events;

trait CanSetResponseTrait
{
    use HoldsResponseTrait;

    public function setResponse(array $response)
    {
        $this->response = $response;
    }
}