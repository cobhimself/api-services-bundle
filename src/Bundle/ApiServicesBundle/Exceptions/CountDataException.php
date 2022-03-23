<?php
/*
 * This file is part of the cobhimself/api-services-bundle package.
 *
 * (c) Collin D. Brooks <collin.brooks@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cob\Bundle\ApiServicesBundle\Exceptions;

use Cob\Bundle\ApiServicesBundle\Models\Config\ResponseModelCollectionConfig;
use Throwable;

/**
 * Exception thrown when count data cannot be determined.
 */
class CountDataException extends BaseApiServicesBundleException
{
    /**
     * @param ResponseModelCollectionConfig $config   the response model collection's configuration
     * @param Throwable|null                $previous a previous exception thrown, if any
     */
    public function __construct(
        ResponseModelCollectionConfig $config,
        Throwable $previous = null
    ) {
        parent::__construct(
            sprintf(
                'Could not get count data for %s using %s command. Using count arguments:' . PHP_EOL . '%s' . PHP_EOL,
                $config->getResponseModelClass(),
                $config->getCountCommand(),
                var_export($config->getCountArgs(), true)
            ),
            $previous
        );
    }
}
