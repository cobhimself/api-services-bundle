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

use Cob\Bundle\ApiServicesBundle\Models\AbstractResponseModel;

/**
 * Final event run after a ResponseModelInterface is loaded.
 *
 * @see AbstractResponseModel::finalizeResponseData
 */
class ResponseModelPostLoadEvent extends ResponseModelEvent
{
    const NAME = 'api_services.response_model.post_load';
}
