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

use Cob\Bundle\ApiServicesBundle\Models\Response\ResponseModel;
use Cob\Bundle\ApiServicesBundle\Models\Util\LogUtil;

class ResponseModelLoadCancelledException extends ResponseModelException
{
    /**
     * @inheritDoc
     */
    public function __construct(
        ResponseModel $model,
        array $commandArgs,
        bool $clearCache,
        string $reason = ""
    ) {
        $message = 'Loading of ' . get_class($model) . ' was cancelled!' . PHP_EOL .
            LogUtil::outputStructure(
                [
                    'Command Args' => json_encode($commandArgs),
                    'Clear Cache' => $clearCache,
                    'Reason' => $reason
                ]
            );

        parent::__construct($message);
    }
}
