<?php

namespace Environaut\Checks;

use Environaut\Command\Command;
use Environaut\Config\Parameters;
use Environaut\Report\Results\Result;
use Environaut\Report\Results\Messages\Message;
use Environaut\Report\Results\Settings\Setting;

/**
 * Class that custom Checks may extend to get access
 * to the dialog helper from the command (and thus
 * the input/output stream).
 */
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

    /**
     * Creates a new Check instance with the given name and parameters.
     *
     * @param string $name name of this check
     * @param array $parameters configurational parameters
     */
    public function __construct($name, array $parameters = array())
    {
        $this->name = $name;
        $this->parameters = new Parameters($parameters);
        $this->result = new Result($this);
    }

    /**
     * @return Result result of this check
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * @return string name of this check
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Adds the given value under the given key to the settings
     * of the result.
     *
     * @param string $name key for that setting
     * @param mixed $value usually a string value
     *
     * @throws \InvalidArgumentException if no valid setting key was given
     */
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

    /**
     * Convenience method to add an informational message to the
     * result (message severity is Message::SEVERITY_INFO).
     *
     * @param string $text message text
     * @param string $name message name
     * @param string $group group name
     */
    protected function addInfo($text = '', $name = null, $group = null)
    {
        if (null === $name)
        {
            $name = $this->name;
        }

        $this->result->addMessage(new Message($name, $text, $group, Message::SEVERITY_INFO));
    }

    /**
     * Convenience method to add notification to the result
     * (message severity is Message::SEVERITY_NOTICE).
     *
     * @param string $text message text
     * @param string $name message name
     * @param string $group group name
     */
    protected function addNotice($text = '', $name = null, $group = null)
    {
        if (null === $name)
        {
            $name = $this->name;
        }

        $this->result->addMessage(new Message($name, $text, $group, Message::SEVERITY_NOTICE));
    }

    /**
     * Convenience method to add an error message to the
     * result (message severity is Message::SEVERITY_ERROR).
     *
     * @param string $text message text
     * @param string $name message name
     * @param string $group group name
     */
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
     * @return \Symfony\Component\Console\Output\OutputInterface
     */
    public function getOutputStream()
    {
        return $this->command->getOutput();
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


