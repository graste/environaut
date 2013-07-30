<?php

namespace Environaut\Report\Results;

use Environaut\Checks\ICheck;
use Environaut\Report\Results\IResult;
use Environaut\Report\Results\Messages\IMessage;
use Environaut\Report\Results\Settings\ISetting;

/**
 * Result that checks provide for a report.
 */
class Result implements IResult
{
    /**
     * Check this result belongs to.
     *
     * @var ICheck check instance
     */
    protected $check;

    /**
     * Messages from the check.
     *
     * @var array of Message instances
     */
    protected $messages = array();

    /**
     * Settings from the check.
     *
     * @var array of Setting instances
     */
    protected $settings = array();

    /**
     * Create a new Result instance.
     */
    public function __construct()
    {
        $this->check = null;
        $this->messages = array();
        $this->settings = array();
    }

    /**
     * Adds the given message to the internal list of messages.
     *
     * @param \Environaut\Report\Results\Messages\IMessage $message
     *
     * @return Result this instance
     */
    public function addMessage(IMessage $message)
    {
        $this->messages[] = $message;
        return $this;
    }

    /**
     * Adds the given setting to the internal list of settings.
     *
     * @param \Environaut\Report\Results\Settings\ISetting $setting
     *
     * @return Result this instance
     */
    public function addSetting(ISetting $setting)
    {
        $this->settings[] = $setting;
        return $this;
    }

    /**
     * Replaces the internal list of settings with the given list.
     *
     * @param array $settings array with ISetting implementing instances
     *
     * @return Result this instance
     */
    public function setSettings(array $settings)
    {
        $this->settings = $settings;
        return $this;
    }

    /**
     * Replaces the internal list of messages with the given list.
     *
     * @param array $messages array with IMessage implementing instances
     *
     * @return Result this instance
     */
    public function setMessages(array $messages)
    {
        $this->messages = $messages;
        return $this;
    }

    /**
     * Returns the internal list of messages emitted by the processed check.
     *
     * @return array of IMessage implementing instances
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * Returns the internal list of settings emitted by the processed check.
     *
     * @return array of ISetting implementing instances
     */
    public function getSettings()
    {
        return $this->settings;
    }

    /**
     * Return all settings or the settings of the specified group as an array.
     *
     * @param string $group group name of settings to return
     *
     * @return array all settings (for the specified group); empty array if group doesn't exist.
     */
    public function getSettingsAsArray($group = null)
    {
        $settings = array();

        foreach ($this->settings as $setting) {
            $settings = array_merge_recursive($settings, $setting->toArray());
        }

        if (null === $group) {
            return $settings;
        } elseif (null !== $group && isset($settings[$group])) {
            return $settings[$group];
        } else {
            return array();
        }
    }

    /**
     * Sets the check instance this result belongs to.
     *
     * @param \Environaut\Checks\ICheck $check check this result belongs to
     *
     * @return Result this instance
     */
    public function setCheck(ICheck $check)
    {
        $this->check = $check;
        return $this;
    }

    /**
     * Returns the check instance this result belongs to.
     *
     * @return \Environaut\Checks\ICheck check instance this result belongs to
     */
    public function getCheck()
    {
        return $this->check;
    }
}
