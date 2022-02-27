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

/**
 * Run after a single ResponseModel has been created and is about to be added
 * to a response model collection.
 *
 * Useful to add initialization callbacks to models across the board.
 *
 * @see AbstractResponseModelCollection::add()
 */
class PostAddModelToCollectionEvent extends ResponseModelCollectionEvent
{
    const NAME = 'api_services.response_model.collection.post_add_model_to_collection';

    /**
     * The ResponseModelInterface associated with this event.
     *
     * @var ResponseModel|null
     */
    private $model;

    /**
     * @param ResponseModel $model      the model which was
     *                                                     just created
     * @param ResponseModelCollection $collection the collection this
     *                                                     model will be
     *                                                     added to
     */
    public function __construct(
        ResponseModel $model,
        ResponseModelCollection $collection
    ) {
        $this->model = $model;

        parent::__construct($collection);
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
