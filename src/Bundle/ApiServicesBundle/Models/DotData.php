<?php

namespace Cob\Bundle\ApiServicesBundle\Models;

use http\Exception\InvalidArgumentException;

class DotData
{
    const RAW_DATA_KEY = '_RAW_DATA';

    /**
     * @var array the data we are holding
     */
    private $data = [];

    /**
     * Cache for our dot functionality.
     *
     * @var array
     */
    private $dotCache = [];

    public function __construct(array $data = null)
    {
        if (!is_null($data)) {
            $this->data = $data;
        }
    }

    public function setRawData($data)
    {
        $this->data[self::RAW_DATA_KEY] = $data;
    }

    public function getRawData()
    {
        if (!isset($this->data[self::RAW_DATA_KEY])) {
            return null;
        } else {
            return $this->data[self::RAW_DATA_KEY];
        }
    }

    public static function of($data): DotData
    {
        if (!is_array($data)) {
            throw new InvalidArgumentException(sprintf(
                '%s requires array data; Given: %s',
                static::class,
                PHP_EOL . $data
            ));
        }

        return new DotData($data);
    }

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
     * @param mixed       $data    when null, the data traversed is the response model's data. However, if provided, the
     *                             data is traversed and the data at the key path is returned (or default if not found);
     *                             no caching is done. Caching is only done for the original full key path when this
     *                             method is called recursively.
     *
     * @return false|mixed By default, if the data cannot be found, false is returned. Otherwise, if a default value has
     *                     been provided, the default will be returned in that case. If data is found at the key path,
     *                     the data found is returned.
     */
    public function dot(string $key, $default = false, $data = null)
    {
        $firstRun = null === $data;

        $data = $data ?? $this->getData();

        if ($key === '') {
            return $data;
        }

        if (empty($data)) {
            return $default;
        }

        //No need to check our dotCache or move forward if we aren't
        //looking for sub-data.
        if (strpos($key, '.') === false) {
            return $data[$key] ?? $default;
        }

        //Have we traversed this path before?
        if (array_key_exists($key, $this->dotCache)) {
            return $this->dotCache[$key];
        }

        //Recurse to get our data
        $parts = explode('.', $key);
        $head = array_shift($parts);
        $tail = implode('.', $parts);
        $value = $this->dot($tail, $default, $data[$head]);

        //Add to our dot cache if we're back at our first run of this method.
        if ($firstRun) {
            $this->dotCache[$key] = $value ?? $default;
        }

        return $value ?? $default;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function toArray(): array
    {
        return $this->getData();
    }

    /**
     * @param array $data
     */
    public function setData(array $data)
    {
        $this->data = $data;
    }
}