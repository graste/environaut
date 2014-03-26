<?php

namespace Environaut\Report\Results\Messages;

use Environaut\Report\Results\Messages\IMessage;

/**
 * A message may be emitted by checks to be presented in a report
 * and supports different severities.
 */
class Message implements IMessage
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
     * @var string
     */
    protected $text;

    /**
     * @var int
     */
    protected $severity;

    public function __construct($name = '', $text = '', $group = null, $severity = IMessage::SEVERITY_INFO)
    {
        $this->name = $name;
        $this->text = $text;
        $this->group = (null === $group) ? 'default' : $group;
        $this->severity = $severity;
    }

    public function getGroup()
    {
        return $this->group;
    }

    public function setGroup($group)
    {
        $this->group = $group;
        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    public function getText()
    {
        return $this->text;
    }

    public function setText($text)
    {
        $this->text = $text;
        return $this;
    }

    public function getSeverity()
    {
        return $this->severity;
    }

    public function setSeverity($severity)
    {
        $this->severity = $severity;
        return $this;
    }

    /**
     * @return array representation of this message
     */
    public function toArray()
    {
        return array(
            'name' => $this->name,
            'text' => $this->text,
            'group' => $this->group,
            'severity' => $this->severity
        );
    }
}
