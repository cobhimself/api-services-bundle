<?php
/*
 * This file is part of the cobhimself/api-services-bundle package.
 *
 * (c) Collin D. Brooks <collin.brooks@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cob\Bundle\ApiServicesBundle\Models;

use GuzzleHttp\Command\ServiceClientInterface as GuzzleServiceClientInterface;

interface ServiceClientInterface extends GuzzleServiceClientInterface
{
    /**
     * Get the cache provider for the service client.
     */
    public function getCache(): CacheProviderInterface;

    /**
     * Set the cache provider for this service client.
     */
    public function setCacheProvider(CacheProviderInterface $cacheProvider) : ServiceClientInterface;

    /**
     * Create an array of commands, each returning a different chunk of the
     * size of the total results.
     *
     * The $buildArgsFunc callable will receive as its first parameter any
     * load arguments provided for the command. Its second parameter is the
     * current start-index and the third parameter is the number of items each
     * response should contain.
     *
     * @param string   $commandName   the name of the command to send the
     *                                service client
     * @param int      $size          the total size of items in the collection
     * @param callable $buildArgsFunc a function to call to compile the
     *                                arguments used with the count command.
     * @param int      $maxResults    the number of results to grab in each
     *                                command
     */
    public function getChunkedCommands(
        string $commandName,
        array $commandArguments,
        int $size,
        callable $buildArgsFunc,
        int $maxResults = 25
    ): array;
}
