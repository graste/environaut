<?php

namespace Environaut\Checks;

use Environaut\Checks\Parameters;
use Environaut\Report\Results\Result;
use Environaut\Report\Messages\Message;
use Environaut\Report\Settings\Setting;
use Environaut\Command\Command;

abstract class Check implements ICheck
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var Parameters
     */
    protected $parameters;

    /**
     * @var Command
     */
    protected $command;

    /**
     * @var Result
     */
    protected $result;

    public function __construct($name, array $parameters = array())
    {
        $this->name = $name;
        $this->parameters = new Parameters($parameters);
        $this->result = new Result($this);
    }

    public function getResult()
    {
        return $this->result;
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

    protected function addNotice($text = '', $name = null, $group = null)
    {
        if (null === $name)
        {
            $name = $this->name;
        }

        $this->result->addMessage(new Message($name, $text, $group, Message::SEVERITY_NOTICE));
    }

    protected function addError($text = '', $name = null, $group = null)
    {
        if (null === $name)
        {
            $name = $this->name;
        }

        $this->result->addMessage(new Message($name, $text, $group, Message::SEVERITY_ERROR));
    }

    /**
     * @return \Symfony\Component\Console\Helper\DialogHelper
     */
    public function getDialogHelper()
    {
        return $this->command->getDialogHelper();
    }

    /**
     * @param \Environaut\Command\Command $command
     */
    public function setCommand(Command $command)
    {
        $this->command = $command;
    }

    /**
     * @return \Environaut\Command\Command
     */
    public function getCommand()
    {
        return $this->command;
    }
}


