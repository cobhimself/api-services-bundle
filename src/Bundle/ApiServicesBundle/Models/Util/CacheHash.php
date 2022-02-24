<?php

namespace Cob\Bundle\ApiServicesBundle\Models\Util;

use Cob\Bundle\ApiServicesBundle\Models\ResponseModelCollectionConfig;

class CacheHash
{
    /**
     * Get a hash representation of the given response class and its arguments for use when setting/getting
     * cache values.
     *
     * @param string $responseClass
     * @param array $commandArgs
     * @return string
     */
    public static function getHashForResponseClassAndArgs(
        string $responseClass,
        array $commandArgs
    ): string {
        ClassUtil::confirmValidResponseModel($responseClass);

        $responseModelConfig = call_user_func_array(
            [$responseClass, 'getResponseModelConfig'],
            [$responseClass]
        );

        return  self::hashArray([
            $responseClass,
            $responseModelConfig->getCommand(),
            $responseModelConfig->getDefaultArgs(),
            $commandArgs
        ]);
    }

    /**
     * Return a unique hash string for the given array.
     */
    protected static function hashArray(array $array): string
    {
        return md5(serialize($array));
    }

    public static function getHashForResponseCollectionClassAndArgs(
        string $collectionClass,
        array $commandArgs
    ): string {
        ClassUtil::confirmValidResponseModelCollection($collectionClass);

        /**
         * @var ResponseModelCollectionConfig $config
         */
        $config = call_user_func_array(
            [$collectionClass, 'getResponseModelCollectionConfig'],
            [$collectionClass]
        );

        return  self::hashArray([
            $collectionClass,
            $config->getCommand(),
            $config->getDefaultArgs(),
            $commandArgs,
            $config->getCountCommand() ?? '',
            join(',', $config->getCountArgs()),
            $config->getLoadMaxResults()
        ]);
    }
}