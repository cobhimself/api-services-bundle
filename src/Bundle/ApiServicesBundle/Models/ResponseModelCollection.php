<?php

namespace Cob\Bundle\ApiServicesBundle\Models;

interface ResponseModelCollection
{
    public static function loadAsync(
        ServiceClientInterface $client,
        array $commandArgs = [],
        array $countCommandArgs = []
    );

    public static function load(
        ServiceClientInterface $client,
        array $commandArgs = [],
        array $countCommandArgs = []
    );

    public static function withData(
        ServiceClientInterface $client,
        array $data = []
    );

    public function toArray(): array;
}