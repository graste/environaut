<?php

namespace Environaut\Cache;

use Environaut\Cache\IReadOnlyCache;
use Environaut\Report\Results\Settings\ISetting;

interface ICache extends IReadOnlyCache
{
    public function add(ISetting $setting);
    public function addAll(array $settings);
    public function save();
}
