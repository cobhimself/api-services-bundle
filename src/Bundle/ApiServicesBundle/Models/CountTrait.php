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

trait CountTrait
{
    /**
     * @var array
     */
    private $collection = [];

    /**
     * @inheritDoc
     */
    public function count(): int
    {
        return count($this->collection);
    }
}
