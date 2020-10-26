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

trait CollectionTrait
{
    /**
     * @var array
     */
    private $collection = [];

    /**
     * @inheritDoc
     */
    public function current()
    {
        return $this->collection[$this->pointer];
    }

    /**
     * @inheritDoc
     */
    public function next()
    {
        ++$this->pointer;
    }

    /**
     * @inheritDoc
     */
    public function key()
    {
        return $this->pointer;
    }

    /**
     * @inheritDoc
     */
    public function valid(): bool
    {
        return isset($this->collection[$this->pointer]);
    }

    /**
     * @inheritDoc
     */
    public function rewind()
    {
        $this->pointer = 0;
    }
}
