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
 * Run right before a response model is to be loaded from cache.
 *
 * @see AbstractResponseModel::load()
 */
class ResponseModelPreLoadFromCacheEvent extends ResponseModelEvent
{
    const NAME = 'api_services.response_model.pre_load_from_cache';

    /**
     * @var CommandInterface
     */
    protected $command;

    /**
     * @param ResponseModelInterface $model   the model which is being loaded
     *                                        from cache
     * @param CommandInterface       $command the command which would have been
     *                                        run had cache not existed
     */
    public function __construct(
        ResponseModelInterface $model,
        CommandInterface $command
    ) {
        $this->command = $command;

        parent::__construct($model);
    }

    /**
     * Get the command associated with this event.
     */
    public function getCommand(): CommandInterface
    {
        return $this->command;
    }
}
