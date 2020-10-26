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

class MockResponseModel extends AbstractResponseModel
{
    /**
     * The service operation the model uses to load its data.
     */
    const LOAD_COMMAND = 'TestCommand';

    /**
     * @var array the arguments to send to the service operation
     */
    const LOAD_ARGUMENTS = ['arg1', 'arg2'];

    /**
     * Key used when setting the raw data for a response model.
     */
    const RAW_DATA_KEY = '_raw_data';

    /**
     * Whether or not this response model only accepts raw data.
     */
    const RAW_DATA = false;
}