<?php

namespace Cob\Bundle\ApiServicesBundle\Models\Loader\Config;

use Cob\Bundle\ApiServicesBundle\Models\ExceptionHandlers\ExceptionHandlerInterface;
use Cob\Bundle\ApiServicesBundle\Models\Response\Collection\ResponseModelCollection;
use Cob\Bundle\ApiServicesBundle\Models\Response\ResponseModel;
use Cob\Bundle\ApiServicesBundle\Models\ServiceClientInterface;

trait LoadConfigBuilderSharedTrait
{
    /**
     * @var array
     */
    protected $commandArgs = [];

    /**
     * @var ResponseModel|ResponseModelCollection
     */
    protected $parent;

    /**
     * @var bool
     */
    protected $clearCache = false;

    /**
     * @var ServiceClientInterface
     */
    protected $client;

    /**
     * @var ExceptionHandlerInterface
     */
    protected $handler;

    /**
     * @var array
     */
    protected $existingData;

    /**
     * @var string
     */
    protected $modelClass;
}
