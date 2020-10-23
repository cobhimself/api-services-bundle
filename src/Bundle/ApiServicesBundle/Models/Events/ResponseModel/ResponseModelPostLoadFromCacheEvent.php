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

use GuzzleHttp\Command\CommandInterface;
use Cob\Bundle\ApiServicesBundle\Models\AbstractResponseModel;
use Cob\Bundle\ApiServicesBundle\Models\ResponseModelInterface;

/**
 * Run after a ResponseModelInterface instance is loaded from cache.
 *
 * @see AbstractResponseModel::load()
 */
class ResponseModelPostLoadFromCacheEvent extends ResponseModelEvent
{
    const NAME = 'api_services.response_model.post_load_from_cache';

    /**
     * The command which would have been run had the response data not
     * been cached.
     *
     * @var CommandInterface
     */
    protected $command;

    /**
     * @var mixed
     */
    protected $cachedData;

    /**
     * @param ResponseModelInterface $model      the model associated with
     *                                           this event
     * @param CommandInterface       $command    the command which would have
     *                                           been run had cache data not
     *                                           existed
     * @param mixed                  $cachedData the data from the cache
     */
    public function __construct(
        ResponseModelInterface $model,
        CommandInterface $command,
        $cachedData
    ) {
        $this->command = $command;
        $this->cachedData = $cachedData;

        parent::__construct($model);
    }

    /**
     * Return the cached data associated with this event.
     *
     * @return mixed
     */
    public function getCachedData()
    {
        return $this->cachedData;
    }
}
