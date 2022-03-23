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

use Cob\Bundle\ApiServicesBundle\Models\Response\Collection\ResponseModelCollection;
use Cob\Bundle\ApiServicesBundle\Models\ResponseModelInterface;

class InvalidCollectionItem extends ResponseModelCollectionException
{
    /**
     * Thrown when an item is attempted to be added to a collection and the
     * expected item class does not match the given item.
     *
     * @param ResponseModelCollectionInterface $collection
     * @param string|ResponseModelInterface    $item       either the item
     *                                                     object itself or the
     *                                                     FQCN of the item
     *
     * @throws ResponseModelSetupException
     */
    public function __construct(
        ResponseModelCollectionInterface $collection,
        string $item
    ) {
        $message = sprintf(
            'Items added to %s must be instances of %s! Found %s instead.',
            get_class($collection),
            $collection::getCollectionClass(),
            is_string($item) ? $item : get_class($item)
        );

        parent::__construct($message);
    }
}
