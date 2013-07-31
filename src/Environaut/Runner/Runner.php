<?php

namespace Environaut\Runner;

use Environaut\Cache\ICache;
use Environaut\Checks\ICheck;
use Environaut\Command\Command;
use Environaut\Config\IConfig;
use Environaut\Config\Parameters;
use Environaut\Report\IReport;
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
    protected $parameters;

    /**
     * @var ICache cache that checks may use for cached settings (storage and retrieval)
     */
    protected $cache;

    public function __construct()
    {
        $this->parameters = new Parameters();
    }
    /**
     * Execute the checks that are defined in the config
     * and generate a report to consume by others.
     */
    public function run()
    {
        $this->initReport();

        $this->initCache();

        $checks = array();
        foreach ($this->config->getCheckDefinitions() as $check_definition) {
            $checks[] = $this->getCheckInstance($check_definition);
        }

        $progress = $this->command->getHelperSet()->get('progress');
        $progress->setFormat(' %current%/%max% [%bar%] %percent%% Elapsed: %elapsed% ');

        $progress->start($this->command->getOutput(), count($checks));

        foreach ($checks as $check) {
            $succeeded = (bool) $check->run();

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
     * Returns a new check instance based on the given parameters (from config).
     *
     * @param array $parameters check definition parameters
     *
     * @return ICheck instance
     */
    protected function getCheckInstance(array $parameters = array())
    {
        $params = new Parameters($parameters);

        $check_implementor = $params->get(IConfig::PARAM_CLASS, self::DEFAULT_CHECK_IMPLEMENTOR);

        $check = new $check_implementor();

        if (!$check instanceof ICheck) {
            throw new \InvalidArgumentException('The given check "' . $check_implementor . '" does not implement ICheck.');
        }

        $check->setCommand($this->command);
        $check->setCache($this->cache);

        $check->setName($params->get(IConfig::PARAM_NAME));
        $check->setGroup($params->get(IConfig::PARAM_GROUP));
        $check->setParameters($params);

        return $check;
    }

    /**
     * Initializes the internal report instance being used to collect the results of the checks.
     *
     * @return IReport report instance
     */
    protected function initReport()
    {
        $report_implementor = $this->config->getReportImplementor();
        $report = new $report_implementor();

        if (!$report instanceof IReport) {
            throw new \InvalidArgumentException('The given report class "' . $report_implementor . '" does not implement IReport.');
        }

        $this->report = $report;
        $this->report->setParameters(new Parameters($this->config->get('report', array())));

        return $this->report;
    }

    /**
     * Initializes the internal cache instance being used by checks to store and retrieve cached settings.
     *
     * @return ICache cache instance
     */
    protected function initCache()
    {
        $cache_implementor = $this->config->getCacheImplementor();
        $cache = new $cache_implementor();

        if (!$cache instanceof ICache) {
            throw new \InvalidArgumentException('The given cache class "' . $cache_implementor . '" does not implement ICache.');
        }

        $this->cache = $cache;
        $this->cache->setParameters(new Parameters($this->config->get('cache', array())));

        return $this->cache;
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
     * Sets the given runtime parameters on the runner.
     *
     * @param Parameters $parameters runtime parameters understood by the runner
     */
    public function setParameters(Parameters $parameters)
    {
        $this->parameters = $parameters;
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
