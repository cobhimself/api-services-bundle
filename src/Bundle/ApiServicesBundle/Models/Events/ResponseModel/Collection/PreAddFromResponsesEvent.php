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

use Cob\Bundle\ApiServicesBundle\Models\ResponseModelCollectionInterface;

/**
 * Run before a collection of response data is added to a collection.
 */
class PreAddFromResponsesEvent extends Event
{
    const NAME = 'api_services.response_model.collection.pre_add_from_responses';

    /**
     * @var array an array of responses we've received from the execution of
     *            commands
     */
    protected $responses;

    /**
     * Run before a set of responses are added to a collection.
     */
    public function __construct(
        ResponseModelCollectionInterface $model,
        array $responses
    ) {
        $this->setResponses($responses);
        parent::__construct($model);
    }

    /**
     * Get the responses which are about to be added.
     */
    public function getResponses(): array
    {
        return $this->responses;
    }

    /**
     * Set the responses which will be added to the collection.
     *
     * Modifications to these responses will impact the data added to the
     * collection!
     *
     * @param array $responses
     */
    public function setResponses(array $responses)
    {
        $this->responses = $responses;
    }
}
