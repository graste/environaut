<?php

namespace Environaut\Report;

use Environaut\Report\IMessage;

class Message implements IMessage
{
    protected $name;
    protected $group;
    protected $text;
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
    }

    public function getName()
    {
        return $this->name;
    }

    public function getText()
    {
        return $this->text;
    }

    public function setText($text)
    {
        $this->text = $text;
    }

    public function getSeverity()
    {
        return $this->severity;
    }

    public function setSeverity($severity)
    {
        $this->severity = $severity;
    }

    public function asArray()
    {
        return array(
            'name' => $this->name,
            'text' => $this->text,
            'group' => $this->group,
            'severity' => $this->severity
        );
    }
}

