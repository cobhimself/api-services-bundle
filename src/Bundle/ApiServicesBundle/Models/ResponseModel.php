<?php

namespace Cob\Bundle\ApiServicesBundle\Models;

interface ResponseModel extends UsesDot
{
    public static function loadAsync(
        ServiceClientInterface $client,
        array $commandArgs
    );

    public static function load(
        ServiceClientInterface $client,
        array $commandArgs
    );

    public static function withData(
        ServiceClientInterface $client,
        array $data
    );

    public function toArray(): array;
}