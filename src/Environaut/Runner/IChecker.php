<?php

namespace Environaut\Runner;

use Environaut\Checks\ICheck;

interface IChecker
{
    public function addCheck(ICheck $check);
    public function setChecks(array $checks);
    public function getReport();
    public function run();
}
