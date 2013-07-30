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
     * @var string
     */
    protected $group;

    /**
     * @var Parameters
     */
    protected $parameters;

    /**
     * @var Command
     */
    protected $command;

    /**
     * @var IResult
     */
    protected $result;

    /**
     * Creates a new Check instance with a random name and the default group.
     */
    public function __construct()
    {
        $this->name = self::getRandomString(8);
        $this->group = $this->getDefaultGroupName();
        $this->parameters = new Parameters(array());
        $this->result = new Result($this);
    }

    /**
     * Returns the result of this check. This should be called after the run() method has been run.
     *
     * @return IResult result of this check
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * Sets the name of the check. When null or a non-string value is given a random name is set.
     *
     * @param string $name name of the check
     */
    public function setName($name = null)
    {
        if (null === $name || !is_string($name)) {
            $this->name = self::getRandomString(8);
        } else {
            $this->name = $name;
        }

        return $this;
    }

    /**
     * Returns the name of this check.
     *
     * @return string name of this check
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets the group name of the check (for reports). Null or non-string values will set the default group name.
     *
     * @param string $group name of the group the check belongs to
     */
    public function setGroup($group = null)
    {
        if (null === $group || !is_string($group)) {
            $this->group = $this->getDefaultGroupName();
        } else {
            $this->group = $group;
        }

        return $this;
    }

    /**
     * Returns the group name of this check that may be useful to group/namespace settings.
     *
     * @return string group name of this check
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * Sets the necessary runtime parameters of the check.
     *
     * @param \Environaut\Config\Parameters $parameters runtime configuration parameters for this check
     */
    public function setParameters(Parameters $parameters)
    {
        $this->parameters = $parameters;
    }

    /**
     * Returns the runtime parameters that are used to run this check.
     *
     * @return Parameters runtime parameters of this check
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * Returns the tokens that should be available prior to running this check.
     *
     * @throws array with token names
     */
    public function getDependencies()
    {
        throw $this->depend_tokens;
    }

    /**
     * Returns the default group name this check uses when none is specified.
     *
     * @return string default group name of the check
     */
    public function getDefaultGroupName()
    {
        return self::DEFAULT_GROUP_NAME;
    }

    /**
     * Returns the default group name this check uses for settings when none is specified.
     *
     * @return string default group name of settings of this check
     */
    public function getDefaultSettingGroupName()
    {
        return $this->getDefaultGroupName();
    }

    /**
     * Adds the given value under the given key to the settings
     * of the result. The setting may have a group name to more
     * easily separate them on export. If no group name is specified
     * the check's default setting group name is used as the default.
     *
     * @param string $name key for that setting
     * @param mixed $value usually a string value
     * @param string $group name of group this setting belongs to
     *
     * @throws \InvalidArgumentException if no valid setting key was given
     */
    protected function addSetting($name, $value, $group = null)
    {
        if (empty($name)) {
            throw new \InvalidArgumentException('A setting must have a valid name.');
        }

        if (null === $group) {
            $group = $this->getDefaultSettingGroupName();
        }

        $this->result->addSetting(new Setting($name, $value, $group));
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
        if (null === $name) {
            $name = $this->name;
        }

        if (null === $group) {
            $group = $this->getDefaultGroupName();
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
        if (null === $name) {
            $name = $this->name;
        }

        if (null === $group) {
            $group = $this->getDefaultGroupName();
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
        if (null === $name) {
            $name = $this->name;
        }

        if (null === $group) {
            $group = $this->getDefaultGroupName();
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

    /**
     * Returns a random string with the specified length and prefix (prefix is not part of the length).
     *
     * @param int $length
     * @param string $prefix
     *
     * @return string
     */
    public static function getRandomString($length = 8, $prefix = 'check_')
    {
        return $prefix . bin2hex(openssl_random_pseudo_bytes($length));
    }
}
