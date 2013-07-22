<?php

namespace Environaut\Report\Results;

use Environaut\Checks\ICheck;
use Environaut\Report\Results\IResult;
use Environaut\Report\Results\Messages\IMessage;
use Environaut\Report\Results\Settings\ISetting;

class Result implements IResult
{
    protected $check;
    protected $messages = array();
    protected $settings = array();

    public function __construct(ICheck $check, array $messages = array(), array $settings = array())
    {
        $this->check = $check;
        $this->messages = $messages;
        $this->settings = $settings;
    }

    public function addMessage(IMessage $message)
    {
        $this->messages[] = $message;
    }

    public function addSetting(ISetting $setting)
    {
        $this->settings[] = $setting;
    }

    public function setSettings(array $settings)
    {
        $this->settings = $settings;
    }

    public function setMessages(array $messages)
    {
        $this->messages = $messages;
    }

    public function getMessages()
    {
        return $this->messages;
    }

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

    public function setCheck(ICheck $check)
    {
        $this->check = $check;
    }

    public function getCheck()
    {
        return $this->check;
    }
}
