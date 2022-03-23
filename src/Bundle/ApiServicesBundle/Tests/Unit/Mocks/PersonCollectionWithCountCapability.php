<?php

namespace Cob\Bundle\ApiServicesBundle\Tests\Unit\Mocks;

use Cob\Bundle\ApiServicesBundle\Models\Config\ResponseModelCollectionConfig;
use Cob\Bundle\ApiServicesBundle\Models\Config\ResponseModelCollectionConfigBuilder;
use Cob\Bundle\ApiServicesBundle\Models\Response\Collection\BaseResponseModelCollection;

/**
 * @codeCoverageIgnore
 */
class PersonCollectionWithCountCapability extends BaseResponseModelCollection
{
    protected static function setup(): ResponseModelCollectionConfigBuilder
    {
        return ResponseModelCollectionConfig::builder()
            ->command('GetPersons')
            ->collectionPath('persons')
            ->countCommand('GetPersonsCount')
            ->childResponseModelClass(Person::class)
            ->countValuePath('total')
            ->chunkCommandMaxResults(2)
            ->buildCountArgsCallback(function ($commandArguments, $index, $maxResults) {
                return [
                    'start-index' => (int) $index,
                    'max-results' => (int) $maxResults
                ];
            });
    }
}
