<?php

namespace Cob\Bundle\ApiServicesBundle\Tests\Unit\Mocks;

use Cob\Bundle\ApiServicesBundle\Models\BaseResponseModelCollection;
use Cob\Bundle\ApiServicesBundle\Models\Config\ResponseModelCollectionConfig;

/**
 * @codeCoverageIgnore
 */
class PersonCollection extends BaseResponseModelCollection
{
    protected static function setup(): ResponseModelCollectionConfig
    {
        $config = new ResponseModelCollectionConfig(
            'GetPersons',
            [],
            'persons'
        );
        $config->setResponseModelClass(static::class);
        $config->setChildResponseModelClass(Person::class);

        return $config;
    }

}
