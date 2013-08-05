<?php

namespace Environaut\Checks;

use Environaut\Checks\ICheck;

interface INestableCheck extends ICheck
{
    public function addChild(ICheck $check);
    public function getChild($name);
    public function getChildren($name);
}
