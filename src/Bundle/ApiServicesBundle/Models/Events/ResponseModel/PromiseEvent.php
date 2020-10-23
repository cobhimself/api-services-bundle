<?php
/*
 * This file is part of the cobhimself/api-services-bundle package.
 *
 * (c) Collin D. Brooks <collin.brooks@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cob\Bundle\ApiServicesBundle\Models\Events\ResponseModel;

use Cob\Bundle\ApiServicesBundle\Exceptions\InvalidResponseModel;
use Cob\Bundle\ApiServicesBundle\Models\ResponseModelSetupTrait;
use Symfony\Component\EventDispatcher\Event;

class PromiseEvent extends Event
{
    use ResponseModelSetupTrait;

    /**
     * @var mixed
     */
    private $context;

    /**
     * @param string|object|null $context the context of this operation; can be
     *                                    an object but must be a valid
     *                                    response model
     *
     * @throws InvalidResponseModel if the context is an object but is not a
     *                              valid response model
     */
    public function __construct($context = null)
    {
        if (!is_string($context) && is_object($context)) {
            static::confirmValidResponseModel($context);
        }

        $this->context = $context;
    }

    public function getContext()
    {
        return $this->context;
    }
}
