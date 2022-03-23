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

use Cob\Bundle\ApiServicesBundle\Exceptions\InvalidResponseModel;
use Cob\Bundle\ApiServicesBundle\Models\Util\Promise;
use GuzzleHttp\Promise\EachPromise;

/**
 * Run before a group of promises are resolved.
 *
 * @see EachPromise
 * @see Promise::all()
 */
class PreRunAllPromisesEvent extends PromiseEvent
{
    const NAME = 'api_services.promise.pre_run_all_promises';

    private $numItems;

    /**
     * Run before a group of promises are run.
     *
     * @param int         $numItems the number of promises in the group of promises
     * @param string|null $context  the context of this to operation; can be an object but must be a valid
     *                              response model
     *
     * @throws InvalidResponseModel
     */
    public function __construct(
        int    $numItems,
        string $context = null
    ) {
        $this->numItems = $numItems;

        parent::__construct($context);
    }

    /**
     * Get the number of items in this group of promises.
     */
    public function getNumItems(): int
    {
        return $this->numItems;
    }
}
