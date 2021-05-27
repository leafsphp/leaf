<?php

namespace Leaf\Helpers;

/**
 * Leaf service container
 * ----
 * A simple container solution for your leaf apps
 * 
 * @since v2.0
 */
class Container implements \ArrayAccess, \Countable, \IteratorAggregate
{
    /**
     * Key-value array of arbitrary data
     * @var array
     */
    protected $data = [];

    /**
     * Constructor
     * @param array $items Pre-populate set with this key-value array
     */
    public function __construct($items = [])
    {
        $this->replace($items);
    }

    /**
     * Normalize data key
     *
     * Used to transform data key into the necessary
     * key format for this set. Used in subclasses
     * like \Leaf\Http\Headers.
     *
     * @param  string $key The data key
     * @return mixed       The transformed/normalized data key
     */
    protected function normalizeKey($key)
    {
        return $key;
    }

    /**
     * Set data key to value
     * @param string $key   The data key
     * @param mixed  $value The data value
     */
    public function set($key, $value)
    {
        $this->data[$this->normalizeKey($key)] = $value;
    }

    /**
     * Get data value with key
     * @param  string $key     The data key
     * @param  mixed  $default The value to return if data key does not exist
     * @return mixed           The data value, or the default value
     */
    public function get($key, $default = null)
    {
        if ($this->has($key)) {
            $isInvokable = is_object($this->data[$this->normalizeKey($key)]) && method_exists($this->data[$this->normalizeKey($key)], '__invoke');

            return $isInvokable ? $this->data[$this->normalizeKey($key)]($this) : $this->data[$this->normalizeKey($key)];
        }

        return $default;
    }

    /**
     * Add data to set
     * @param array $items Key-value array of data to append to this set
     */
    public function replace($items)
    {
        foreach ($items as $key => $value) {
            $this->set($key, $value); // Ensure keys are normalized
        }
    }

    /**
     * Set the content-type of response to json
     */
    public function contentJson()
    {
        $this->replace(['Content-Type' => 'text/json']);
    }

    /**
     * Set the content-type of response to html
     */
    public function contentHtml()
    {
        $this->replace(['Content-Type' => 'text/html']);
    }

    /**
     * Fetch set data
     * @return array This set's key-value data array
     */
    public function all()
    {
        return $this->data;
    }

    /**
     * Fetch set data keys
     * @return array This set's key-value data array keys
     */
    public function keys()
    {
        return array_keys($this->data);
    }

    /**
     * Does this set contain a key?
     * @param  string  $key The data key
     * @return boolean
     */
    public function has($key)
    {
        return array_key_exists($this->normalizeKey($key), $this->data);
    }

    /**
     * Remove value with key from this set
     * @param  string $key The data key
     */
    public function remove($key)
    {
        unset($this->data[$this->normalizeKey($key)]);
    }

    /**
     * Property Overloading
     */

    public function __get($key)
    {
        return $this->get($key);
    }

    public function __set($key, $value)
    {
        $this->set($key, $value);
    }

    public function __isset($key)
    {
        return $this->has($key);
    }

    public function __unset($key)
    {
        $this->remove($key);
    }

    /**
     * Clear all values
     */
    public function clear()
    {
        $this->data = [];
    }

    /**
     * Array Access
     */

    public function offsetExists($offset)
    {
        return $this->has($offset);
    }

    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    public function offsetSet($offset, $value)
    {
        $this->set($offset, $value);
    }

    public function offsetUnset($offset)
    {
        $this->remove($offset);
    }

    /**
     * Countable
     */

    public function count()
    {
        return count($this->data);
    }

    /**
     * IteratorAggregate
     */

    public function getIterator()
    {
        return new \ArrayIterator($this->data);
    }

    /**
     * Ensure a value or object will remain globally unique
     * 
     * @param  string   $key   The value or object name
     * @param  \Closure $value The closure that defines the object
     * 
     * @return mixed
     */
    public function singleton($key, $value)
    {
        $this->set($key, function ($c) use ($value) {
            static $object;

            if (null === $object) {
                $object = $value($c);
            }

            return $object;
        });
    }

    /**
     * Protect closure from being directly invoked
     * @param  \Closure $callable A closure to keep from being invoked and evaluated
     * @return \Closure
     */
    public function protect(\Closure $callable)
    {
        return function () use ($callable) {
            return $callable;
        };
    }
}
