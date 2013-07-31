<?php

namespace Environaut\Runner;

use Environaut\Command\Command;
use Environaut\Config\IConfig;
use Environaut\Config\Parameters;

/**
 * Interface all check running instances must implement. Runners
 * should get the necessary data via setters, then run() should
 * be called and the report getter used afterwards.
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
     * Sets the command on the runner to have access to the input and output.
     *
     * @param Command $command
     */
    public function setCommand(Command $command);

    /**
     * Sets the given runtime parameter on the runner.
     *
     * @param Parameters $parameters runtime options understood by the runner
     */
    public function setParameters(Parameters $parameters);

    /**
     * Execute the checks that are defined in the config and generate a report to consume by others.
     */
    public function run();

    /**
     * @return IReport report created by the runner
     */
    public function getReport();
}
