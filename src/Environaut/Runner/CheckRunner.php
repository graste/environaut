<?php

namespace Environaut\Runner;

use Environaut\Check\ICheck;
use Environaut\Report\Report;
use Environaut\Command\Command;

class CheckRunner implements IChecker
{
    protected $checks = array();
    protected $report;
    protected $command;

    public function __construct(array $checks, Command $command)
    {
        $this->setChecks($checks);
        $this->command = $command;
        $this->report = new Report();
    }

    public function setChecks(array $checks)
    {
        $this->checks = $checks;
    }

    public function addCheck(ICheck $check)
    {
        $this->checks[] = $check;
    }

    public function getChecks()
    {
        return $this->checks;
    }

    public function run()
    {
        $progress = $this->command->getHelperSet()->get('progress');
        $progress->start($this->command->getOutput(), count($this->checks));

        foreach ($this->checks as $check) {
            /* @var $result Environaut\Report\IResult */
            $result = $check->process();
            $this->report->addResult($result);
            usleep(250000);
            $progress->advance();
        }
        $progress->finish();
    }

    public function getReport()
    {
        return $this->report;
    }
}

