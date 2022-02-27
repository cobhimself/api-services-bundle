<?php

namespace Cob\Bundle\ApiServicesBundle\Tests\Unit\Mocks;

use Cob\Bundle\ApiServicesBundle\Models\BaseResponseModelCollection;
use Cob\Bundle\ApiServicesBundle\Models\ResponseModelCollectionConfig;

/**
 * @codeCoverageIgnore
 */
class PersonCollectionWithCountCapability extends BaseResponseModelCollection
{
    protected static function setup(): ResponseModelCollectionConfig
    {
        $config = new ResponseModelCollectionConfig(
            'GetPersons',
            [],
            'persons',
            'GetPersonsCount'
        );
        $config->setResponseModelClass(static::class);
        $config->setChildResponseModelClass(Person::class);
        $config->setCountValuePath('total');
        $config->setChunkCommandMaxResults(2);
        $config->setBuildCountArgsCallback(function ($commandArguments, $index, $maxResults) {
            return [
                'start-index' => (int) $index,
                'max-results' => (int) $maxResults
            ];
        });

        return $config;
    }
}