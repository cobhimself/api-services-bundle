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
 * Run after a collection of response data has been added to a collection.
 */
class PostAddFromResponsesEvent extends Event
{
    const NAME = 'api_services.response_model.collection.post_add_from_responses';

    /**
     * @var array an array of responses we've received from the execution of
     *            commands
     */
    protected $responses;

    /**
     * Run after a set of responses have been added to a collection.
     */
    public function __construct(
        ResponseModelCollectionInterface $model,
        array $responses
    ) {
        $this->responses = $responses;
        parent::__construct($model);
    }

    /**
     * Get the responses which were added.
     */
    public function getResponses(): array
    {
        return $this->responses;
    }
}
