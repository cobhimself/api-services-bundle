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

use Cob\Bundle\ApiServicesBundle\Models\Response\ResponseModel;
use Cob\Bundle\ApiServicesBundle\Models\Response\Collection\ResponseModelCollection;
use Cob\Bundle\ApiServicesBundle\Models\Config\ResponseModelCollectionConfig;

/**
 * Run after a single ResponseModel has been created and is about to be added
 * to a response model collection.
 *
 * Useful to add initialization callbacks to models across the board.
 */
class PostAddModelToCollectionEvent extends ResponseModelCollectionEvent
{
    const NAME = 'api_services.response_model.collection.post_add_model_to_collection';

    /**
     * The ResponseModel associated with this event.
     *
     * @var ResponseModel|null
     */
    private $model;

    /**
     * @param ResponseModel $model      the model which was just created
     */
    public function __construct(
        ResponseModelCollectionConfig $config,
        ResponseModel $model
    ) {
        $this->model = $model;

        parent::__construct($config);
    }

    /**
     * Get the model being loaded.
     *
     * @return ResponseModel|null
     */
    public function getModel()
    {
        return $this->model;
    }
}
