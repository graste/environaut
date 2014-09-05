<?php

namespace Environaut\Checks;

use Environaut\Config\Parameters;

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
     * Sets the name of the check.
     *
     * @param string $name name of the check
     */
    public function setName($name);

    /**
     * Returns the group name of the check.
     *
     * @return string group name of the check
     */
    public function getGroup();

    /**
     * Sets the group name of the check (for reports).
     *
     * @param string $group name of the group the check belongs to
     */
    public function setGroup($group);

    /**
     * Returns the runtime parameters of the check.
     *
     * @return \Environaut\Config\Parameters instance with runtime parameters
     */
    public function getParameters();

    /**
     * Sets the necessary runtime parameters of the check.
     *
     * @param \Environaut\Config\Parameters $parameters runtime configuration parameters for this check
     */
    public function setParameters(Parameters $parameters);

    /**
     * Returns the default group name this check uses when none is specified.
     *
     * @return string default group name of the check
     */
    public function getDefaultGroupName();

    /**
     * Returns the default group name this check uses for settings when none is specified.
     *
     * @return string default group name of settings of this check
     */
    public function getDefaultSettingGroupName();

    /**
     * Execute the check and add messages and settings to the result.
     *
     * @return boolean true if check succeeded; false otherwise
     */
    public function run();

    /**
     * Return the result of the check after it ran (consisting of messages and settings).
     *
     * @return \Environaut\Report\Results\IResult the result of the check
     */
    public function getResult();

    /**
     * Return dependencies that must be fulfilled for this check to run.
     *
     * @return array of simple string tokens that are necessary to run the check
     */
    public function getDependencies();
}
