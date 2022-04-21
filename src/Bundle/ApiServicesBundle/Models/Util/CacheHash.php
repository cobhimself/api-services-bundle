<?php

namespace Cob\Bundle\ApiServicesBundle\Models\Util;

use Cob\Bundle\ApiServicesBundle\Models\Config\ResponseModelCollectionConfig;
use Symfony\Component\Console\Output\OutputInterface;

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
        array $commandArgs,
        OutputInterface $output = null
    ): string {
        ClassUtil::confirmValidResponseModel($responseClass);

        $config = call_user_func_array(
            [$responseClass, 'getConfig'],
            [$responseClass]
        );

        if (!is_null($output)) {
            LogUtil::debug($output, 'Generating cache hash for ' . $responseClass . ' using: ');
            LogUtil::outputStructure([
                'Command' => $config->getCommand(),
                'Default Args' => $config->getDefaultArgs(),
                'Command Args' => $commandArgs
            ]);
        }

        return  self::hashArray([
            $responseClass,
            $config->getCommand(),
            $config->getDefaultArgs(),
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
        array $commandArgs,
        OutputInterface $output = null
    ): string {
        ClassUtil::confirmValidResponseModelCollection($collectionClass);

        /**
         * @var ResponseModelCollectionConfig $config
         */
        $config = call_user_func_array(
            [$collectionClass, 'getConfig'],
            [$collectionClass]
        );

        if (!is_null($output)) {
            LogUtil::debug($output, 'Generating cache hash for ' . $collectionClass . ' using: ');
            LogUtil::outputStructure([
                'Command' => $config->getCommand(),
                'Default Args' => $config->getDefaultArgs(),
                'Command Args' => $commandArgs,
                'Count Command' => $config->getCountCommand() ?? '',
                'Count Args' => join(',', $config->getCountArgs()),
                'Load Max Results' => $config->getLoadMaxResults()
            ]);
        }

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
