<?php

namespace Cob\Bundle\ApiServicesBundle\Tests\Unit\Mocks;

use Cob\Bundle\ApiServicesBundle\Models\BaseResponseModelCollection;
use Cob\Bundle\ApiServicesBundle\Models\Config\ResponseModelCollectionConfig;
use Cob\Bundle\ApiServicesBundle\Models\Config\ResponseModelCollectionConfigBuilder;

/**
 * @codeCoverageIgnore
 */
class PersonCollection extends BaseResponseModelCollection
{
    protected static function setup(): ResponseModelCollectionConfigBuilder
    {
        return ResponseModelCollectionConfig::builder()
            ->command('GetPersons')
            ->collectionPath('persons')
            ->childResponseModelClass(Person::class);
    }
}
