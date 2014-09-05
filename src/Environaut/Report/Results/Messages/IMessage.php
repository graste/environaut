<?php

namespace Environaut\Report\Results\Messages;

interface IMessage
{
    const SEVERITY_FATAL = 1;
    const SEVERITY_ERROR = 2;
    const SEVERITY_WARN = 4;
    const SEVERITY_NOTICE = 8;
    const SEVERITY_INFO = 16;
    const SEVERITY_DEBUG = 32;
    const SEVERITY_ALL = 4294967295;

    public function getGroup();
    public function getName();
    public function getSeverity();
    public function getText();
    public function toArray();
}
