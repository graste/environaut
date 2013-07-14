<?php

namespace Environaut\Report\Results;

use Environaut\Checks\ICheck;
use Environaut\Report\Messages\IMessage;
use Environaut\Report\Settings\ISetting;

interface IResult
{
    public function addMessage(IMessage $message);
    public function getMessages();
    public function setMessages(array $messages);

    public function addSetting(ISetting $setting);
    public function getSettings();
    public function setSettings(array $settings);

    public function setCheck(ICheck $check);
    public function getCheck();
}

