<?php

namespace Environaut\Config;

/**
 * Class that wraps an associative array for
 * more convenient access of keys and values.
 */
class Parameters
{
    /**
     * @var array
     */
    protected $parameters = array();

    /**
     * Create a new instance with the given parameters as
     * initial value set.
     *
     * @param array $parameters
     */
    public function __construct(array $parameters = array())
    {
        $this->parameters = $parameters;
    }

    /**
     * Returns the value for the given key.
     *
     * @param string $key name of key
     * @param mixed $default value to return if key doesn't exist
     *
     * @return mixed value for that key or default given
     */
    public function get($key, $default = null)
    {
        if (isset($this->parameters[$key]) || array_key_exists($key, $this->parameters)) {
            return $this->parameters[$key];
        }

        return $default;
    }

    /**
     * Sets a given value for the specified key.
     *
     * @param string $key name of entry
     * @param mixed $value value to set for the given key
     *
     * @return mixed the value set
     */
    public function set($key, $value)
    {
        return $this->parameters[$key] = $value;
    }

    /**
     * Returns whether the key exists or not.
     *
     * @param string $key name of key to check
     *
     * @return bool true, if key exists; false otherwise
     */
    public function has($key)
    {
        if (isset($this->parameters[$key]) || array_key_exists($key, $this->parameters)) {
            return true;
        }

        return false;
    }

    /**
     * Returns all first level key names.
     *
     * @return array of keys
     */
    public function getKeys()
    {
        return array_keys($this->parameters);
    }

    /**
     * Returns the data as an associative array.
     *
     * @return array with all data
     */
    public function toArray()
    {
        return $this->parameters;
    }

    /**
     * Delete all key/value pairs.
     */
    public function clear()
    {
        $this->parameters = array();
    }
}
