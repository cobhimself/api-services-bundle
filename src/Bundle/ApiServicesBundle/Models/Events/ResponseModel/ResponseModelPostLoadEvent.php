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

use Cob\Bundle\ApiServicesBundle\Models\ResponseModelConfig;

/**
 * Final event run after a ResponseModelInterface is loaded.
 */
class ResponseModelPostLoadEvent extends ResponseModelEvent
{
    const NAME = 'api_services.response_model.post_load';
    /**
     * @var array
     */
    private $commandArgs;
    private $response;

    public function __construct(ResponseModelConfig $config, array $commandArgs, $response)
    {
        $this->commandArgs = $commandArgs;
        $this->response = $response;

        parent::__construct($config);
    }

    /**
     * @return array
     */
    public function getCommandArgs(): array
    {
        return $this->commandArgs;
    }

    /**
     * @return mixed
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @param mixed $response
     */
    public function setResponse($response)
    {
        $this->response = $response;
    }
}
