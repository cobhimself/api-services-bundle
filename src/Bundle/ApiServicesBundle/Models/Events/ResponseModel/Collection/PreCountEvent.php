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

/**
 * Run before the count information is retrieved for a collection.
 */
class PreCountEvent extends ResponseModelCollectionEvent
{
    const NAME = 'api_services.response_model.collection.pre_count';
}
