<?php

namespace Environaut\Checks;

class Parameters
{
    protected $parameters = array();

    public function __construct(array $parameters = array())
    {
        $this->parameters = $parameters;
    }

    public function get($name, $default_value = null)
    {
        if (isset($this->parameters[$name]) || array_key_exists($name, $this->parameters)) {
            return $this->parameters[$name];
        }

        return $default_value;
    }

    public function set($name, $value)
    {
        $this->parameters[$name] = $value;
    }

    public function has($name)
    {
        if(isset($this->parameters[$name]) || array_key_exists($name, $this->parameters)) {
            return true;
        }

        return false;
    }

    public function getNames()
    {
        return array_keys($this->parameters);
    }

    public function getAll()
    {
        return $this->parameters;
    }

    public function clear()
    {
        $this->parameters = array();
    }
}
