<?php

namespace Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection;

use Cob\Bundle\ApiServicesBundle\Models\Events\CanGetCommandTrait;
use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\ResponseModelCollectionEvent;
use Cob\Bundle\ApiServicesBundle\Models\ResponseModelCollectionConfig;
use GuzzleHttp\Command\CommandInterface;

class PostExecuteCommandEvent extends ResponseModelCollectionEvent
{
    use CanGetCommandTrait;

    const NAME = 'api_services.response_model.collection.post_execute_command';

    /**
     * @var array
     */
    private $response;

    public function __construct(
        ResponseModelCollectionConfig $config,
        CommandInterface $command,
        array $response
    ) {
        parent::__construct($config);
        $this->command = $command;
        $this->response = $response;
    }

    /**
     * @return array
     */
    public function getResponse(): array
    {
        return $this->response;
    }

    /**
     * @param array $response
     */
    public function setResponse(array $response)
    {
        $this->response = $response;
    }
}