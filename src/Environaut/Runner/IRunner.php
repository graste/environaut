<?php

namespace Environaut\Runner;

use Environaut\Command\Command;
use Environaut\Config\IConfig;

/**
 * Interface all check running instances must implement.
 */
interface IRunner
{
    /**
     * Sets the given config on the runner.
     *
     * @param IConfig $config config data
     */
    public function setConfig(IConfig $config);

    /**
     * Sets the command on the runner to have
     * access to the input and output.
     *
     * @param Command $command
     */
    public function setCommand(Command $command);

    /**
     * @return IReport report created by the runner
     */
    public function getReport();

    /**
     * Execute the checks that are defined in the config
     * and generate a report to consume by others.
     */
    public function run();
}
