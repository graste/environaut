<?php

namespace Environaut\Report\Results\Settings;

use Environaut\Report\Results\Settings\ISetting;

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

    public function toArray()
    {
        return array($this->name => $this->value);
    }
}

