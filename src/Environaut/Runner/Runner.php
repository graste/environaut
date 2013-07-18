<?php

namespace Environaut\Runner;

use Environaut\Config\IConfig;
use Environaut\Command\Command;
use Environaut\Report\Results\IResult;
use Environaut\Runner\IRunner;

/**
 * Default runner used for environment checks.
 * Reads checks from the config, executes them
 * in that order and compiles the results into
 * a report.
 */
class Runner implements IRunner
{
    /**
     * @var IConfig current config
     */
    protected $config;

    /**
     * @var Command environaut command
     */
    protected $command;

    /**
     * @var IReport current report
     */
    protected $report;

    /**
     * Create new instance with given config and command.
     *
     * @param IConfig $config
     * @param Command $command
     */
    public function __construct(IConfig $config, Command $command)
    {
        $this->setConfig($config);
        $this->setCommand($command);
    }

    /**
     * Sets the given config on the runner.
     *
     * @param IConfig $config config data
     */
    public function setConfig(IConfig $config)
    {
        $this->config = $config;
    }

    /**
     * Sets the command on the runner to have
     * access to the input and output.
     *
     * @param Command $command
     */
    public function setCommand(Command $command)
    {
        $this->command = $command;
    }

    /**
     * Execute the checks that are defined in the config
     * and generate a report to consume by others.
     */
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
            $check->run();
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

    /**
     * @return IReport report created by the runner
     */
    public function getReport()
    {
        return $this->report;
    }
}
