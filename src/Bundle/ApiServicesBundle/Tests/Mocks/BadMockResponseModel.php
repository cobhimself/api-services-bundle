<?php
/*
 * This file is part of the cobhimself/api-services-bundle package.
 *
 * (c) Collin D. Brooks <collin.brooks@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cob\Bundle\ApiServicesBundle\Tests\Mocks;

use Cob\Bundle\ApiServicesBundle\Models\AbstractResponseModel;

class BadMockResponseModel extends AbstractResponseModel
{
    const RAW_DATA_KEY = null;
    const LOAD_ARGUMENTS = null;
}