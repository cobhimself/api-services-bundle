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

use Cob\Bundle\ApiServicesBundle\Models\Config\ResponseModelCollectionConfig;
use Cob\Bundle\ApiServicesBundle\Models\Response\ResponseModel;

/**
 * Run after data is added to a collection from a parent model.
 */
class PostAddDataFromParentEvent extends ResponseModelCollectionEvent
{
    const NAME = 'api_services.response_model.collection.post_add_data_from_parent';

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
        ResponseModelCollectionConfig $modelConfig,
        array $data
    ) {
        $this->parentModel = $parentModel;
        $this->data = $data;

        parent::__construct($modelConfig);
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
}
