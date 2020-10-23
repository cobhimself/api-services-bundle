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

use FilterIterator;
use Iterator;

class CollectionFilter extends FilterIterator
{
    /**
     * @var callable
     */
    private $filter;

    /**
     * @inheritDoc
     */
    public function accept(): bool
    {
        $item = $this->getInnerIterator()->current();
        $func = $this->filter;

        return $func($item);
    }

    public function __construct(Iterator $iterator, callable $filter)
    {
        $this->filter = $filter;
        parent::__construct($iterator);
    }
}
