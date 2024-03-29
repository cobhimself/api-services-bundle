<?php
/*
 * This file is part of the cobhimself/api-services-bundle package.
 *
 * (c) Collin D. Brooks <collin.brooks@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cob\Bundle\ApiServicesBundle\Models\Response\Collection;

use Cob\Bundle\ApiServicesBundle\Exceptions\CountDataException;
use Cob\Bundle\ApiServicesBundle\Models\Config\ResponseModelCollectionConfig;
use Cob\Bundle\ApiServicesBundle\Models\DotData;
use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\PostCountEvent;
use Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel\Collection\PreCountEvent;
use Cob\Bundle\ApiServicesBundle\Models\ExceptionHandlers\ClientCommandExceptionHandler;
use Cob\Bundle\ApiServicesBundle\Models\Response\BaseResponseModel;
use Cob\Bundle\ApiServicesBundle\Models\ServiceClientInterface;
use Cob\Bundle\ApiServicesBundle\Models\Util\Promise;
use GuzzleHttp\Promise\PromiseInterface;

/**
 * Retrieves count information for a response model.
 */
class Count extends BaseResponseModel
{
    /**
     * Get count data for the given response model asynchronously.
     *
     * @param ResponseModelCollectionConfig $config the configuration for the response model collection
     * @param ServiceClientInterface        $client the service client to use to retrieve
     *                                              count information
     *
     * @return PromiseInterface
     *
     */
    public static function getAsync(
        ResponseModelCollectionConfig $config,
        ServiceClientInterface $client
    ): PromiseInterface {
        return Promise::async(function () use ($config, $client) {
            $client->dispatchEvent(
                PreCountEvent::class,
                $config
            );

            $countCommand = $config->getCountCommand();
            $countArgs = $config->getCountArgs();
            $countValuePath = $config->getCountValuePath();

            $command = $client->getCommand($countCommand, $countArgs);

            $response = $client->execute($command);

            $count = DotData::of($response)->dot($countValuePath, false);

            $client->dispatchEvent(
                PostCountEvent::class,
                $config,
                $count
            );

            return $count;
        })->otherwise(function ($reason) use ($config) {
            //Unfortunately, we can't just ignore an issue like this...
            ClientCommandExceptionHandler::passThruAndWrapWith(
                CountDataException::class,
                [$config]
            )->handle($reason);
        });
    }

    /**
     * Get count data for the given response model.
     *
     * @param ResponseModelCollectionConfig $config the config for the response model collection
     * @param ServiceClientInterface        $client the service client to use to retrieve
     *                                              count information
     *
     * @return int
     *
     */
    public static function get(
        ResponseModelCollectionConfig $config,
        ServiceClientInterface $client
    ): int {
        return static::getAsync($config, $client)->wait();
    }
}
