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

use GuzzleHttp\Promise\PromiseInterface;
use Cob\Bundle\ApiServicesBundle\Exceptions\CountDataException;
use Cob\Bundle\ApiServicesBundle\Exceptions\InvalidResponseModel;
use Cob\Bundle\ApiServicesBundle\Exceptions\ResponseModelSetupException;
use Cob\Bundle\ApiServicesBundle\Exceptions\UnknownCommandException;
use Cob\Bundle\ApiServicesBundle\Models\ExceptionHandlers\ClientCommandExceptionHandler;
use Cob\Bundle\ApiServicesBundle\Models\Util\Promise;

/**
 * Retrieves count information for a response model.
 */
class Count
{
    /**
     * Get count data for the given response model asynchronously.
     *
     * @param ServiceClient $client      the service client to use to retrieve
     *                                   count information
     * @param string        $model       the fully-qualified class name of a
     *                                   response model to obtain count
     *                                   information for
     * @param array         $commandArgs additional arguments to use with the
     *                                   COUNT_COMMAND when obtaining count data
     *
     * @return PromiseInterface
     *
     * @throws InvalidResponseModel
     * @throws UnknownCommandException
     * @throws ResponseModelSetupException
     */
    public static function dataForAsync(
        ServiceClient $client,
        string $model,
        array $commandArgs = []
    ): PromiseInterface {
        static::confirmValidResponseModel($model);

        $arguments = call_user_func([$model, 'getCountArguments']);
        //Add in any arguments sent in
        $arguments = array_merge($arguments, $commandArgs);

        $count = new self([], null, $client);

        $command = $client->getCommand(
            call_user_func([$model, 'getCountCommand']),
            $arguments
        );

        return Promise::async(function () use (
            $client,
            $count,
            $model,
            $command
        ) {
            $response = $client->executeAsync($command)->wait();
            $count->setData($response);

            return $count->dot(call_user_func([$model, 'getCountValuePath']));
        })->otherwise(function ($reason) use ($command, $model, $arguments) {
            //Unfortunately, we can't just ignore an issue like this...
            ClientCommandExceptionHandler::passThruAndWrapWith(
                CountDataException::class,
                [$command, $model, $arguments]
            )->handle($reason);
        });
    }

    /**
     * Get count data for the given response model.
     *
     * @param ServiceClient $client the service client to use to retrieve
     *                              count information
     * @param string        $model  the fully-qualified class name of a response
     *                              model to obtain count information for
     *
     * @return PromiseInterface
     *
     * @throws InvalidResponseModel
     * @throws UnknownCommandException
     * @throws ResponseModelSetupException
     */
    public static function dataFor(
        ServiceClient $client,
        string $model
    ): PromiseInterface {
        return static::dataForAsync($client, $model)->wait();
    }
}
