<?php

namespace Cob\Bundle\ApiServicesBundle\Models\Response;

use Cob\Bundle\ApiServicesBundle\Exceptions\IncorrectParentResponseModel;
use Cob\Bundle\ApiServicesBundle\Models\Response\Collection\ResponseModelCollection;

interface HasParent
{
    /**
     * Get the parent model of this model if available.
     *
     * @return ResponseModel|ResponseModelCollection|null
     */
    public function getParent();

    /**
     * Determine if this model has a parent model.
     *
     * @return bool
     */
    public function hasParent(): bool;

    /**
     * @param ResponseModel|ResponseModelCollection $model
     *
     * @return mixed
     *
     * @throws IncorrectParentResponseModel if the given model is not a {@link ResponseModel} or a
     *                                      {@link ResponseModelCollection}.
     */
    public function setParent($model);
}
