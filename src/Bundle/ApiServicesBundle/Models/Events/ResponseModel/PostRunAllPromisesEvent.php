<?php
/*
 * This file is part of the cobhimself/api-services-bundle package.
 *
 * (c) Collin D. Brooks <collin.brooks@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel;

use GuzzleHttp\Promise\EachPromise;
use Cob\Bundle\ApiServicesBundle\Models\Util\Promise;

/**
 * Run after a group of promises have been generated and loaded.
 *
 * @see EachPromise
 * @see Promise::all()
 */
class PostRunAllPromisesEvent extends PromiseEvent
{
    const NAME = 'api_services.promise.post_run_all_promises';
}
