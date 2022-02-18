<?php

namespace Cob\Bundle\ApiServicesBundle\Models;

interface UsesDot
{
    /**
     * Helper method which aids in the traversal of data within the
     * response model.
     *
     * If a dot path has been resolved before, the value is returned without
     * having to traverse the data structure thanks to caching.
     *
     * @example For the given array structure:
     *
     * $data = [
     *    'one' => 1,
     *    'parent' => [
     *        'child1' => [
     *             'child2' => true
     *        ]
     *    ]
     * ];
     *
     * $this->dot('one'); //1
     * $this->dot('parent'); //['child1' => ['child2' => true]]
     * $this->dot('parent.child1'); //['child2' => true]
     * $this->dot('parent.child1.child2'); //true
     * $this->dot('parent.child1.child3'); //false
     * #this->dot('parent.child1.child3', 'my_default'); //'my_default'
     *
     * @param string      $key     the key to use as the path to find the data
     * @param false|mixed $default if the key path cannot be found, or if the key is empty, return this value
     *
     * @return false|mixed By default, if the data cannot be found, false is returned. Otherwise, if a default value has
     *                     been provided, the default will be returned. If data is found at the key path, the data found
     *                     is returned.
     */
    public function dot(string $key, $default = false);
}