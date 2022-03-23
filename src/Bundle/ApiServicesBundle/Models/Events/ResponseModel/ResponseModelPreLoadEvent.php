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

use Cob\Bundle\ApiServicesBundle\Models\Config\ResponseModelConfig;

/**
 * Run before a response model is loaded.
 *
 * The command arguments used to load the model can be modified using the
 * setCommandArgs method. You can also cancel the loading of the model by using
 * the cancelLoad method. If you want the model to fail when it has been
 * cancelled, use the setFailOnCancel method.
 *
 * @see AbstractResponseModel::getResponseDataLoadPromise
 */
class ResponseModelPreLoadEvent extends ResponseModelEvent
{
    const NAME = 'api_services.response_model.pre_load';

    /**
     * @var array
     */
    private $commandArgs;

    /**
     * @var bool
     */
    private $clearCache;

    /**
     * @var bool
     */
    private $cancelLoad = false;

    /**
     * @var bool
     */
    private $failOnCancel = false;

    /**
     * @var string
     */
    private $cancelReason = '';

    /**
     * @param ResponseModelConfig $config      the model about to be loaded
     * @param array               $commandArgs the arguments being used with
     *                                         the LOAD_COMMAND of the model
     * @param bool                $clearCache  whether or not the cache for
     *                                            the model should be cleared
     */
    public function __construct(
        ResponseModelConfig $config,
        array $commandArgs,
        bool $clearCache = false
    ) {
        parent::__construct($config);
        $this->commandArgs = $commandArgs;
        $this->clearCache = $clearCache;
    }

    /**
     * @return array
     */
    public function getCommandArgs(): array
    {
        return $this->commandArgs;
    }

    /**
     * @return bool
     */
    public function doClearCache(): bool
    {
        return $this->clearCache;
    }

    /**
     * @param array $commandArgs
     *
     * @return ResponseModelPreLoadEvent
     */
    public function setCommandArgs(
        array $commandArgs
    ): ResponseModelPreLoadEvent {
        $this->commandArgs = $commandArgs;

        return $this;
    }

    /**
     * @param bool $clearCache
     *
     * @return ResponseModelPreLoadEvent
     */
    public function setClearCache(bool $clearCache): ResponseModelPreLoadEvent
    {
        $this->clearCache = $clearCache;

        return $this;
    }

    /**
     * Specify whether or not this response model's loading should be cancelled.
     *
     * @param bool $cancel
     *
     * @return $this
     */
    public function cancelLoad(bool $cancel): ResponseModelPreLoadEvent
    {
        $this->cancelLoad = $cancel;

        return $this;
    }

    /**
     * Whether or not we've determined we should cancel the loading of this
     * response model.
     *
     * @return bool
     */
    public function loadCancelled(): bool
    {
        return $this->cancelLoad;
    }

    /**
     * Whether or not we should fail the loading of the model if we've decided
     * to cancel the loading.
     *
     * @return bool
     */
    public function failOnCancel(): bool
    {
        return $this->failOnCancel;
    }

    /**
     * Set whether or not we should fail the loading of the model if we've
     * decided to cancel the loading.
     *
     * @param bool $failOnCancel
     *
     * @return ResponseModelPreLoadEvent
     */
    public function setFailOnCancel(
        bool $failOnCancel
    ): ResponseModelPreLoadEvent {
        $this->failOnCancel = $failOnCancel;

        return $this;
    }

    /**
     * Get the reason message as to why the loading was cancelled.
     *
     * @return string
     */
    public function getCancelReason(): string
    {
        return $this->cancelReason;
    }

    /**
     * A message which describes why the loading was cancelled.
     *
     * @param string $cancelReason
     *
     * @return ResponseModelPreLoadEvent
     */
    public function setCancelReason(
        string $cancelReason
    ): ResponseModelPreLoadEvent {
        $this->cancelReason = $cancelReason;

        return $this;
    }
}
