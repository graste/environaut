<?php

namespace Environaut\Checks;

/**
 * Interface all checks must implement. Checks deliver IResult instances with messages and settings.
 */
interface ICheck
{
    /**
     * Default group name used to store settings of this check.
     */
    const DEFAULT_GROUP_NAME = 'default';

    /**
     * Returns the name of the check.
     *
     * @return string name of the check
     */
    public function getName();

    /**
     * Returns the group name of the check.
     *
     * @return string group name of the check
     */
    public function getGroup();

    /**
     * Execute the check and add messages and settings to the result.
     */
    public function run();

    /**
     * Return the result of the check (consisting of messages and settings)
     *
     * @return IResult the result of the check
     */
    public function getResult();

    /**
     * Return dependencies that must be fulfilled for this check to run.
     *
     * @return array of simple string tokens that are necessary to run the check
     */
    public function getDependencies();
}
