<?php

namespace Environaut\Config;

use Environaut\Config\IConfig;
use Environaut\Checks\Parameters;

class Config implements IConfig
{
    protected $config;

    public function __construct(array $config_data = array())
    {
        $this->config = new Parameters($config_data);
    }

    public function getCheckDefinitions()
    {
        return $this->config->get('checks', array());
    }

    public function getExportImplementor()
    {
        return $this->config->get('export_implementor', 'Environaut\Report\Exporter');
    }

    public function getReportImplementor()
    {
        return $this->config->get('report_implementor', 'Environaut\Report\Report');
    }

    public function getRunnerImplementor()
    {
        return $this->config->get('runner_implementor', 'Environaut\Runner\CheckRunner');
    }
}
