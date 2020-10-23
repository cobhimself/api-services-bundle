<?php
/*
 * This file is part of the cobhimself/api-services-bundle package.
 *
 * (c) Collin D. Brooks <collin.brooks@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel;

use Cob\Bundle\ApiServicesBundle\Models\ResponseModelCollectionInterface;
use Cob\Bundle\ApiServicesBundle\Models\ResponseModelInterface;

/**
 * Run when a parent model is being associated with a model.
 */
class AssociateParentModelEvent extends ResponseModelEvent
{
    const NAME = 'api_services.response_model.associate_parent_model';

    /**
     * The parent model.
     *
     * @var ResponseModelInterface|ResponseModelCollectionInterface|null
     */
    private $parent;

    /**
     * @param ResponseModelInterface|ResponseModelCollectionInterface $model  the model having its parent set
     * @param ResponseModelInterface|ResponseModelCollectionInterface $parent the collection this model will be added to
     */
    public function __construct(
        $model,
        $parent
    ) {
        parent::__construct($model);
        $this->parent = $parent;
    }

    /**
     * Get the parent model.
     *
     * @return ResponseModelInterface|ResponseModelCollectionInterface|null
     */
    public function getParentModel()
    {
        return $this->parent;
    }
}
