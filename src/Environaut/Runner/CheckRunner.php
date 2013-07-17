<?php

namespace Environaut\Runner;

use Environaut\Config\IConfig;
use Environaut\Command\Command;
use Environaut\Report\Results\IResult;
use Environaut\Runner\IChecker;

class CheckRunner implements IChecker
{
    protected $config;
    protected $command;
    protected $report;

    public function __construct(IConfig $config, Command $command)
    {
        $this->setConfig($config);
        $this->setCommand($command);
    }

    public function setConfig(IConfig $config)
    {
        $this->config = $config;
    }

    public function setCommand(Command $command)
    {
        $this->command = $command;
    }

    public function run()
    {
        $report_implementor = $this->config->getReportImplementor();
        $this->report = new $report_implementor();

        $checks = array();
        foreach ($this->config->getCheckDefinitions() as $check) {
            $test = new $check['class']($check['name'], $check);
            $test->setCommand($this->command);
            $checks[] = $test;
        }

        $progress = $this->command->getHelperSet()->get('progress');
        $progress->setFormat(PHP_EOL . ' %current%/%max% [%bar%] %percent%% Elapsed: %elapsed%' . PHP_EOL . PHP_EOL);

        $progress->start($this->command->getOutput(), count($checks));

        foreach ($checks as $check) {
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
