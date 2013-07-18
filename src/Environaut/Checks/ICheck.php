<?php

namespace Environaut\Checks;

/**
 * Interface all checks must implement. Checks
 * deliver IResult instances with messages and
 * settings.
 */
interface ICheck
{
    /**
     * @return string name of the check
     */
    public function getName();

    /**
     * Execute the check.
     */
    public function run();

    /**
     * @return IResult the result of the check
     */
    public function getResult();

    /**
     * tbd
     */
    public function getDependencyManager();
}

