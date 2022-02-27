<?php

namespace Cob\Bundle\ApiServicesBundle\Tests\Unit\Mocks;

use Cob\Bundle\ApiServicesBundle\Models\BaseResponseModel;
use Cob\Bundle\ApiServicesBundle\Models\ResponseModelConfig;

/**
 * @codeCoverageIgnore
 */
class Person extends BaseResponseModel
{
    /**
     * @var PersonCollectionWithCountCapability
     */
    private $children;

    public static function setup(): ResponseModelConfig
    {
        $config = new ResponseModelConfig("GetPerson", []);
        $config->setResponseModelClass(static::class);

        return $config;
    }

    public function getName(): string
    {
        return $this->dot('name', '');
    }

    public function getAge(): string
    {
        return $this->dot('age', '');
    }

    public function isAlive()
    {
        return $this->dot('alive', null);
    }

    public function getChildren(): PersonCollection
    {
        if (is_null($this->children)) {
            $this->children = PersonCollection::withData($this->getClient(), $this->dot('children'));
        }

        return $this->children;
    }
}