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
        $message = sprintf(
            'Loading of %s was cancelled!' . PHP_EOL
            . 'Command args: %s' . PHP_EOL
            . 'Clear cache: %s' . PHP_EOL
            . 'Reason: %s',
            get_class($model),
            var_export($commandArgs, true),
            $clearCache ? 'true' : 'false',
            $reason
        );

        parent::__construct($message);
    }
}
