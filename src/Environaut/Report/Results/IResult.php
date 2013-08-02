<?php

namespace Environaut\Report\Results;

use Environaut\Checks\ICheck;
use Environaut\Report\Results\Messages\IMessage;
use Environaut\Report\Results\Settings\ISetting;

/**
 * Interface that check results must implement.
 */
interface IResult
{
    /**
     * Check run was a failure and did not succeed.
     */
    const FAIL = 'fail';

    /**
     * Check ran successfully.
     */
    const SUCCESS = 'success';

    /**
     * Check was not yet run or processed.
     */
    const UNEXECUTED = 'unexecuted';

    /**
     * Check result should not be used as something failed during or prior execution.
     */
    const INVALID = 'invalid';

    /**
     * Adds the given message to the internal list of messages.
     *
     * @param IMessage $message
     */
    public function addMessage(IMessage $message);

    /**
     * Returns the internal list of messages emitted by the processed check.
     *
     * @return array of IMessage implementing instances
     */
    public function getMessages();

    /**
     * Replaces the internal list of messages with the given list.
     *
     * @param array $messages array with IMessage implementing instances
     */
    public function setMessages(array $messages);

    /**
     * Adds the given setting to the internal list of settings.
     *
     * @param ISetting $setting setting to add to the internal lists of settings
     * @param bool $cachable whether or not the setting may be put into a cache for reuse on re-execution of the check
     *
     * @return Result this instance for fluent API support
     */
    public function addSetting(ISetting $setting, $cachable = true);

    /**
     * Adds the given setting to the internal list of settings.
     *
     * @param \Environaut\Report\Results\Settings\ISetting $setting setting to add
     * @param bool $cachable whether or not the setting may be put into a cache for reuse on re-execution of the check
     */
    public function addSettings(array $setting, $cachable = true);

    /**
     * Returns the internal list of settings emitted by the processed check.
     *
     * @return array of ISetting implementing instances
     */
    public function getSettings();

    /**
     * Returns the internal list of cachable settings emitted by the processed check.
     *
     * @return array of ISetting implementing instances
     */
    public function getCachableSettings();

    /**
     * Return all settings or the settings of the specified group as an array.
     *
     * @param string $group group name of settings to return
     *
     * @return array all settings (for the specified group); empty array if group doesn't exist.
     */
    public function getSettingsAsArray($group = null);

    /**
     * Return all cachable settings or the cachable settings of the specified group as an array.
     *
     * @param string $group group name of cachable settings to return
     *
     * @return array all cachable settings (for the specified group); empty array if group doesn't exist.
     */
    public function getCachableSettingsAsArray($group = null);

    /**
     * Sets the status of the current result (which may be of interest for the later export and cache writing).
     *
     * @param string $status one of the constants from IResult (like IResult::SUCCESS or IResult::FAIL)
     */
    public function setStatus($status = self::INVALID);

    /**
     * Returns the current status of this result (usually one of the IResult::SUCCESS or IResult::FAIL constants).
     *
     * @return string status of this result instance
     */
    public function getStatus();

    /**
     * Sets the check instance this result belongs to.
     *
     * @param \Environaut\Checks\ICheck $check check this result belongs to
     */
    public function setCheck(ICheck $check);

    /**
     * Returns the check instance this result belongs to.
     *
     * @return \Environaut\Checks\ICheck check instance this result belongs to
     */
    public function getCheck();
}
