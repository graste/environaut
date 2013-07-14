<?php

namespace Environaut\Check;

interface ICheck
{
    public function getName();
    public function process();
    public function getResult();
    public function getDependencyManager();
}

