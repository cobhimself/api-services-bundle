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

use Cob\Bundle\ApiServicesBundle\Models\ResponseModelCollection;

/**
 * Run after count information is retrieved for a collection.
 */
class PostCountEvent extends ResponseModelCollectionEvent
{
    const NAME = 'api_services.response_model.collection.post_count';

    private $countData;

    /**
     * @inheritDoc
     */
    public function __construct(
        ResponseModelCollection $collection,
        array $countData
    ) {
        $this->countData = $countData;

        parent::__construct($collection);
    }

    /**
     * @return array
     */
    public function getCountData(): array
    {
        return $this->countData;
    }
}
