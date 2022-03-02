<?php

namespace Cob\Bundle\ApiServicesBundle\Models;

use Cob\Bundle\ApiServicesBundle\Exceptions\IncorrectParentResponseModel;
use Cob\Bundle\ApiServicesBundle\Models\Util\ClassUtil;

trait HasParentTrait
{
    /**
     * @var ResponseModel|ResponseModelCollection|null
     */
    protected $parent;

    /**
     * @return ResponseModel|ResponseModelCollection|null
     */
    public function getParent()
    {
        return $this->parent;
    }

    public function hasParent(): bool
    {
        return !is_null($this->parent);
    }

    public function setParent($model)
    {
        if (
            !ClassUtil::isValidResponseModel($model)
            && !ClassUtil::isValidResponseModelCollection($model)
        ) {
            throw new IncorrectParentResponseModel(
                $model,
                ResponseModel::class . ' OR ' . ResponseModelCollection::class,
                get_class($model)
            );
        }

        $this->parent = $model;
    }
}