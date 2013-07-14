<?php

namespace Environaut\Report\Settings;

interface ISetting
{
    public function getName();
    public function getValue();
    public function asArray();
}

