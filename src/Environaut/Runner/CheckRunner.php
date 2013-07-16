<?php

namespace Environaut\Runner;

use Environaut\Checks\ICheck;
use Environaut\Command\Command;
use Environaut\Report\Report;
use Environaut\Report\Results\IResult;
use Environaut\Runner\IChecker;

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
        $progress->setFormat(PHP_EOL . ' %current%/%max% [%bar%] %percent%% Elapsed: %elapsed%' . PHP_EOL . PHP_EOL);

        $progress->start($this->command->getOutput(), count($this->checks));

        foreach ($this->checks as $check) {
            $check->process();
            $result = $check->getResult();
            if (!$result instanceof IResult) {
                throw new \LogicException('The "process" method of check "' . $check->getName() . '" (class "' . get_class($check) . '") must return a result that implements IResult.');
            }
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
