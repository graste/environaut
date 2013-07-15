<?php

namespace Environaut\Checks;

interface ICheck
{
    public function getName();
    public function process();
    public function getResult();
    public function getDependencyManager();
}
