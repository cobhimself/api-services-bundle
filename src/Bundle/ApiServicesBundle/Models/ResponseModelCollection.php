<?php

namespace Cob\Bundle\ApiServicesBundle\Models;

interface ResponseModelCollection extends ResponseModel
{
    public function toArray(): array;
}