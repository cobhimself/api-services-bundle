<?php

namespace Cob\Bundle\ApiServicesBundle\Tests\Unit\Mocks;

use Cob\Bundle\ApiServicesBundle\Models\Config\ResponseModelConfig;
use Cob\Bundle\ApiServicesBundle\Models\Config\ResponseModelConfigBuilder;
use Cob\Bundle\ApiServicesBundle\Models\Response\BaseResponseModel;

/**
 * @codeCoverageIgnore
 */
class Person extends BaseResponseModel
{
    /**
     * @var PersonCollection
     */
    private $children;

    public static function setup(): ResponseModelConfigBuilder
    {
        return ResponseModelConfig::builder()
         ->command("GetPerson");
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
            $this->children = PersonCollection::using($this->getClient())
                ->withData($this->dot('children'));
        }

        return $this->children;
    }
}
