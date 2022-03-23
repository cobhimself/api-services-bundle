<?php

namespace Cob\Bundle\ApiServicesBundle\Models\Response;

use Cob\Bundle\ApiServicesBundle\Models\Response\Collection\ResponseModelCollection;
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
        ClassUtil::confirmValidParentModel($model, $this);

        $this->parent = $model;
    }
}
