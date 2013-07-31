<?php

namespace Environaut\Cache;

use Environaut\Config\Parameters;
use Environaut\Report\Results\Settings\ISetting;

interface ICache
{
    public function setLocation($location);
    public function setParameters(Parameters $parameters);

    public function add(ISetting $setting);
    public function get($name, $group, $flag);
    public function getAll($groups, $flag);
}
