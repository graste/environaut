<?php

namespace Environaut\Runner;

use Environaut\Checks\Check;
use Environaut\Checks\ICheck;
use Environaut\Command\Command;
use Environaut\Config\IConfig;
use Environaut\Config\Parameters;
use Environaut\Report\Results\IResult;
use Environaut\Runner\IRunner;

/**
 * Reads checks from the config, executes them in that order and compiles the results into a report.
 */
class Runner implements IRunner
{
    /**
     * Default namespaced class name to use for checks if none is specified in the check definition of the config.
     */
    const DEFAULT_CHECK_IMPLEMENTOR = 'Environaut\Checks\Configurator';

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
     * @var Parameters
     */
    protected $options;

    /**
     * Execute the checks that are defined in the config
     * and generate a report to consume by others.
     */
    public function run()
    {
        $report_implementor = $this->config->getReportImplementor();
        $this->report = new $report_implementor();

        $checks = array();
        foreach ($this->config->getCheckDefinitions() as $check_definition) {
            $checks[] = $this->getCheckInstance($check_definition);
        }

        $progress = $this->command->getHelperSet()->get('progress');
        $progress->setFormat(PHP_EOL . ' %current%/%max% [%bar%] %percent%% Elapsed: %elapsed%' . PHP_EOL . PHP_EOL);

        $progress->start($this->command->getOutput(), count($checks));

        foreach ($checks as $check) {
            $check->run();

            $result = $check->getResult();
            if (!$result instanceof IResult) {
                throw new \LogicException(
                    'The result of check "' . $check->getName() . '" (group "' . $check->getGroup() . '", class "' .
                    get_class($check) . '") must implement IResult.'
                );
            }
            $this->report->addResult($result);

            $progress->advance();
        }

        $progress->finish();
    }

    /**
     * Returns a new check instance based on the given parameters.
     *
     * @param array $parameters check definition parameters
     *
     * @return ICheck instance
     */
    protected function getCheckInstance(array $parameters = array())
    {
        $params = new Parameters($parameters);

        $name = $params->get(IConfig::PARAM_NAME, Check::getRandomString(8, 'check_'));
        $group = $params->get(IConfig::PARAM_GROUP, ICheck::DEFAULT_GROUP_NAME);
        $class = $params->get(IConfig::PARAM_CLASS, self::DEFAULT_CHECK_IMPLEMENTOR);

        unset($parameters[IConfig::PARAM_CLASS]);
        unset($parameters[IConfig::PARAM_NAME]);
        unset($parameters[IConfig::PARAM_GROUP]);

        $check = new $class($name, $group, $parameters);
        $check->setCommand($this->command);

        if (!$check instanceof ICheck) {
            throw new \InvalidArgumentException('The given check "' . $class . '" does not implement ICheck.');
        }

        return $check;
    }

    /**
     * Returns the report that contains the results of the checks.
     *
     * @return IReport report created by the runner
     */
    public function getReport()
    {
        return $this->report;
    }

    /**
     * Sets the given options on the runner.
     *
     * @param array $options associative array with options understood by the runner
     */
    public function setOptions(array $options = array())
    {
        $this->options = new Parameters($options);
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
}
