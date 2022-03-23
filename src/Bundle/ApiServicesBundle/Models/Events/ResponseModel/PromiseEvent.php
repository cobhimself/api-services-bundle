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
use Cob\Bundle\ApiServicesBundle\Models\Response\Collection\ResponseModelCollection;
use Cob\Bundle\ApiServicesBundle\Models\Response\ResponseModel;
use Symfony\Component\EventDispatcher\Event;

class PromiseEvent extends Event
{
    /**
     * @var mixed
     */
    private $context;

    /**
     * @param string|ResponseModel|ResponseModelCollection|null $context the context of this operation; can be
     *                                                                   an object but must be a valid
     *                                                                   response model
     *
     * @throws InvalidResponseModel if the context is an object but is not a
     *                              valid response model
     */
    public function __construct($context = null)
    {
        $this->context = $context;
    }

    public function getContext()
    {
        return $this->context;
    }
}
