<?php

namespace Environaut\Runner;

use Environaut\Command\Command;
use Environaut\Config\IConfig;

interface IChecker
{
    public function setConfig(IConfig $config);
    public function setCommand(Command $command);
    public function getReport();
    public function run();
}
