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

use Cob\Bundle\ApiServicesBundle\Models\ResponseModelCollectionConfig;

/**
 * Run after count information is retrieved for a collection.
 */
class PostCountEvent extends ResponseModelCollectionEvent
{
    const NAME = 'api_services.response_model.collection.post_count';

    /**
     * @var int
     */
    private $count;

    /**
     * @inheritDoc
     */
    public function __construct(
        ResponseModelCollectionConfig $config,
        int                           $count
    ) {
        $this->count = $count;

        parent::__construct($config);
    }

    /**
     * @return int
     */
    public function getCount(): int
    {
        return $this->count;
    }
}
