<?php

namespace Environaut\Report\Settings;

use Environaut\Report\Settings\ISetting;

class Setting implements ISetting
{
    protected $name;
    protected $value;

    public function __construct($name, $value)
    {
        $this->name = $name;
        $this->value = $value;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function asArray()
    {
        return array($this->name => $this->value);
    }
}

