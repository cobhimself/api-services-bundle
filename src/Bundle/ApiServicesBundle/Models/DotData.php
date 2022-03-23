<?php

namespace Cob\Bundle\ApiServicesBundle\Models;

/**
 * Class whose responsibility is to store primarily structured array data which can be traversed through the use of
 * dot notation.
 *
 * There are certain instances where the data held by a DotData instance is desired to be 'raw' (or mixed). If that is
 * the case, you can set and retrieve this raw data using the raw data getter and setter.
 */
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

    public function __construct($data = null)
    {
        if (is_array($data)) {
            $this->data = $data;
        } else if (!is_null($data)) {
            $this->setRawData($data);
        }
    }

    /**
     * Set the raw data for this instance to store.
     *
     * Raw data is usually helpful only for instances where the data we are storing is not structured (like file
     * contents, etc.). Storing raw data is done by using the `DotData::RAW_DATA_KEY` within this dot data's data array
     * and does not preclude the instance from storing normal structured data as long as the raw data key is not used.
     *
     * @see DotData::getRawData()
     *
     * @param mixed $data the raw data this instance should store
     */
    public function setRawData($data)
    {
        $this->data[self::RAW_DATA_KEY] = $data;
    }

    /**
     * Get the raw data stored in this instance.
     *
     * @return mixed|null
     */
    public function getRawData()
    {
        if (!isset($this->data[self::RAW_DATA_KEY])) {
            return null;
        } else {
            return $this->data[self::RAW_DATA_KEY];
        }
    }

    /**
     * Quickly get a DotData instance containing the given data.
     *
     * @param array|mixed $data if an array, a new DotData instance with structured data is returned; otherwise, the
     *                          data is considered 'raw' and the returned DotData will not hold structured data.
     *
     * @return DotData the newly constructed DotData instance
     */
    public static function of($data): DotData
    {
        $dotData = new DotData();

        if (is_array($data)) {
            $dotData->setData($data);
        } else {
            $dotData->setRawData($data);
        }

        return $dotData;
    }

    /**
     * Helper method which aids in the traversal of data within our structured data.
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

        if (isset($data[$head])) {
            $value = $this->dot($tail, $default, $data[$head]);
        }

        //Add to our dot cache if we're back at our first run of this method.
        if ($firstRun) {
            $this->dotCache[$key] = $value ?? $default;
        }

        return $value ?? $default;
    }

    /**
     * Return the data this instance is storing.
     *
     * NOTE: if raw data has been stored in this instance, it will be accessible by the DotData::RAW_DATA_KEY.
     *
     * @return array the data associated with this instance
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * Return our data array.
     *
     * This method is simply a more explicit version of `getData`.
     *
     * @return array the data array we are holding
     */
    public function toArray(): array
    {
        return $this->getData();
    }

    /**
     * Set the structure data for this instance.
     *
     * @param array $data the data for this instance to store
     */
    public function setData(array $data)
    {
        $this->data = $data;
    }
}
