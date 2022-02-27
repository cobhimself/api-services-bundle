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

use Cob\Bundle\ApiServicesBundle\Models\Events\CanGetCommandArgsTrait;
use Cob\Bundle\ApiServicesBundle\Models\Events\CanSetCommandArgsTrait;
use Cob\Bundle\ApiServicesBundle\Models\ResponseModelCollectionConfig;

/**
 * Run before any loading is done in the collection.
 */
class PreLoadEvent extends ResponseModelCollectionEvent
{
    use CanGetCommandArgsTrait;
    use CanSetCommandArgsTrait;

    const NAME = 'api_services.response_model.collection.pre_load';

    public function __construct(ResponseModelCollectionConfig $config, array $commandArgs = [])
    {
        parent::__construct($config);
        $this->commandArgs = $commandArgs;
    }
}
