<?php
/*
 * This file is part of the cobhimself/api-services-bundle package.
 *
 * (c) Collin D. Brooks <collin.brooks@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection;

use Cob\Bundle\ApiServicesBundle\Models\ResponseModel;
use Cob\Bundle\ApiServicesBundle\Models\ResponseModelCollection;
use Cob\Bundle\ApiServicesBundle\Models\ResponseModelCollectionInterface;
use Cob\Bundle\ApiServicesBundle\Models\ResponseModelInterface;

/**
 * Run before data is added to a collection from a parent model.
 */
class PreAddDataFromParentEvent extends ResponseModelCollectionEvent
{
    const NAME = 'api_services.response_model.collection.pre_add_data_from_parent';

    /**
     * @var ResponseModel
     */
    private $parentModel;

    /**
     * @var array
     */
    private $data;

    /**
     * Run before data is added to a collection from a parent model.
     */
    public function __construct(
        ResponseModel $parentModel,
        ResponseModelCollection $collection,
        array $data
    ) {
        $this->parentModel = $parentModel;
        $this->data = $data;

        parent::__construct($collection);
    }

    public function getParentModel(): ResponseModel
    {
        return $this->parentModel;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * Set the data which will be added to the collection.
     *
     * @param array $data
     */
    public function setData(array $data)
    {
        $this->data = $data;
    }
}
