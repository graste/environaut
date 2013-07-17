<?php

namespace Environaut\Config;

interface IConfig
{
    public function getCheckDefinitions();
    public function getExportImplementor();
    public function getRunnerImplementor();
    public function getReportImplementor();
//    public function getResultImplementor();
//    public function getReportFormatterImplementor();
}
