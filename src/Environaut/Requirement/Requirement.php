<?php

namespace Environaut\Requirement;

class Requirement
{
    protected $name;
    protected $parameters;
    protected $fulfilled;
    protected $info_message;
    protected $help_message;
    protected $mandatory;

    public function __construct($name, array $parameters = array()) {
        $this->name = (string) $name;
        $this->parameters = (array) $parameters;
        $this->fulfilled = (bool) (isset($this->parameters['fulfilled'] && $this->parameters['fulfilled'] === true) ? true : false;
        $this->mandatory = (bool) (isset($this->parameters['mandatory'] && $this->parameters['mandatory'] === true) ? true : false;
        $this->info_message = (string) (isset($this->parameters['info_message'])) ? $this->parameters['info_message'] : '';
        $this->help_message = (string) (isset($this->parameters['help_message'])) ? $this->parameters['help_message'] : '';
    }

    public function getName() {
        return $this->name;
    }

    public function isFulfilled() {
        return $this->fulfilled;
    }

    public function getInfoMessage() {
        return $this->info_message;
    }

    public function getHelpMessage() {
        return $this->help_message;
    }

    public function isMandatory() {
        return $this->mandatory;
    }
}
