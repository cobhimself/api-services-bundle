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

use Cob\Bundle\ApiServicesBundle\Models\ResponseModelCollectionInterface;
use Cob\Bundle\ApiServicesBundle\Models\ResponseModelInterface;

/**
 * Exception called when a model is expected to be an instance of
 * ResponseModelInterface or ResponseModelCollectionInterface but is not.
 */
class InvalidResponseModel extends BaseApiServicesBundleException
{
    /**
     * @param string $class the FQCN of the model we were checking
     */
    public function __construct(string $class)
    {
        $message = sprintf(
           '%s must implement one of: ' . PHP_EOL . "\t%s" . PHP_EOL . "\t%s",
           $class,
           ResponseModelCollectionInterface::class,
           ResponseModelInterface::class
       );

        parent::__construct($message);
    }
}
