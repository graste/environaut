<?php

namespace Fixtures;

use Environaut\Config\Config;

class TestConfigHandler extends \Environaut\Config\ConfigHandler
{
    public function getConfig()
    {
        return new Config(array('introduction' => 'introductory text'));
    }
}
