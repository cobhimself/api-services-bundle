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

use Cob\Bundle\ApiServicesBundle\Models\Events\CanGetCollectionLoadConfigTrait;
use Cob\Bundle\ApiServicesBundle\Models\Events\CanGetResponseTrait;
use Cob\Bundle\ApiServicesBundle\Models\Events\CanSetResponseTrait;
use Cob\Bundle\ApiServicesBundle\Models\Loader\Config\CollectionLoadConfig;
use Cob\Bundle\ApiServicesBundle\Models\Config\ResponseModelCollectionConfig;

/**
 * The final event run after a ResponseModelCollectionInterface instance
 * is loaded.
 */
class PostLoadEvent extends ResponseModelCollectionEvent
{
    use CanGetResponseTrait;
    use CanSetResponseTrait;
    use CanGetCollectionLoadConfigTrait;

    const NAME = 'api_services.response_model.collection.post_load';

    public function __construct(
        ResponseModelCollectionConfig $config,
        CollectionLoadConfig $loadConfig,
        array $response = []
    ) {
        parent::__construct($config);
        $this->response = $response;
        $this->loadConfig = $loadConfig;
    }
}
