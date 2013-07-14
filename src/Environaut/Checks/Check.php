<?php

namespace Environaut\Checks;

use Environaut\Report\Results\Result;
use Environaut\Report\Messages\Message;
use Environaut\Report\Settings\Setting;
use Environaut\Command\Command;

abstract class Check implements ICheck
{
    protected $name;
    protected $parameters = array();
    protected $command;
    protected $result;

    public function __construct($name, array $parameters = array())
    {
        $this->name = $name;
        $this->parameters = $parameters;
        $this->result = new Result($this);
    }

    public function getResult()
    {
        return $this->result;
    }

    public function setCommand(Command $command)
    {
        $this->command = $command;
    }

    public function getName()
    {
        return $this->name;
    }

    protected function addSetting($name, $value)
    {
        if (empty($name))
        {
            throw new \InvalidArgumentException('A setting must have a valid name.');
        }
        $this->result->addSetting(new Setting($name, $value));
    }

    public function getDependencyManager()
    {
        throw new \Exception('Not yet implemented');
    }

    protected function addInfo($text = '', $name = null, $group = null)
    {
        if (null === $name)
        {
            $name = $this->name;
        }

        $this->result->addMessage(new Message($name, $text, $group, Message::SEVERITY_INFO));
    }

    protected function addError($text = '', $name = null, $group = null)
    {
        if (null === $name)
        {
            $name = $this->name;
        }

        $this->result->addMessage(new Message($name, $text, $group, Message::SEVERITY_ERROR));
    }
}

