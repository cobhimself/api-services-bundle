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
 * The final event run after a ResponseModelCollectionInterface instance
 * is loaded.
 */
class PostLoadEvent extends Event
{
    const NAME = 'api_services.response_model.collection.post_load';
}
