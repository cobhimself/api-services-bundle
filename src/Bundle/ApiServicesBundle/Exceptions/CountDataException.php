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

use GuzzleHttp\Command\CommandInterface;
use Throwable;

/**
 * Exception thrown when count data cannot be determined.
 */
class CountDataException extends BaseApiServicesBundleException
{
    /**
     * @param CommandInterface $command   the command used to grab count data
     * @param string           $model     the response model the count data was
     *                                    attempted to be retrieved from
     * @param array            $arguments arguments used with the given command
     *                                    to load count data
     */
    public function __construct(
        CommandInterface $command,
        string $model,
        array $arguments,
        Throwable $previous
    ) {
        parent::__construct(
            sprintf(
                'Could not get count data for %s using %s command. Using count arguments:' . PHP_EOL . '%s' . PHP_EOL,
                $model,
                $command->getName(),
                var_export($arguments, true)
            ),
            $previous
        );
    }
}
