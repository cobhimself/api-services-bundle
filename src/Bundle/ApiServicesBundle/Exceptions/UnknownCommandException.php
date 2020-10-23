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

use GuzzleHttp\Command\Guzzle\DescriptionInterface;
use Throwable;

/**
 * Exception thrown when a command is attempted to be retrieved but none exists
 * within the Description object.
 */
class UnknownCommandException extends BaseApiServicesBundleException
{
    /**
     * @param string               $desiredCommand the command we we're
     *                                             attempting to load
     * @param DescriptionInterface $description    the Description object our
     *                                             service client is using
     * @param Throwable|null       $previous       any previous exception thrown
     */
    public function __construct(
        string $desiredCommand,
        DescriptionInterface $description,
        Throwable $previous = null
    ) {
        //We'll compose a list of commands which are similar to the one we
        //attempted to call so our exception message is a bit more helpful.
        $percents = [];

        foreach ($description->getOperations() as $opName => $operation) {
            similar_text($desiredCommand, $opName, $percent);
            $percents[(string) $percent] = $opName;
        }

        krsort($percents);
        $topMatches = implode(', ', array_slice($percents, 0, 2));

        $message = sprintf(
            'Cannot find command %s in description %s. Did you mean %s?',
            $desiredCommand,
            $description->getName(),
            $topMatches
        );

        parent::__construct($message, $previous);
    }
}
