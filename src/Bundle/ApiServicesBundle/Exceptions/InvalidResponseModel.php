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
use Cob\Bundle\ApiServicesBundle\Models\Response\Collection\ResponseModelCollection;

/**
 * Exception called when a model is expected to be an instance of
 * ResponseModel or ResponseModelCollection but is not.
 */
class InvalidResponseModel extends BaseApiServicesBundleException
{
    /**
     * @param string $class the FQCN of the model we were checking
     */
    public function __construct(string $class, array $acceptableClasses)
    {
        $message = "$class must implement";

        if (count($acceptableClasses) > 1) {
            $message .= " one of";
        }

        $message .= ":\n\t" . join("\n\t", $acceptableClasses);

        parent::__construct($message);
    }
}
