<?php

namespace Environaut\Report\Results\Settings;

interface ISetting
{
    public function getName();
    public function getValue();
    public function toArray();
}

