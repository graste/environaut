<?php

namespace Environaut\Tests\Export\Formatter;

use Environaut\Config\Parameters;
use Environaut\Export\Formatter\ShellSettingsWriter;
use Environaut\Report\Report;
use Environaut\Report\Results\Result;
use Environaut\Report\Results\Settings\Setting;
use Environaut\Tests\BaseTestCase;

class ShellSettingsWriterTest extends BaseTestCase
{
    public function testBasic()
    {
        $this->runShellFormatter();
    }

    public function testCapitalized()
    {
        $this->runShellFormatter(
            array(
                'capitalize_names' => true
            ),
            'simple_capitalized.sh'
        );
    }

    public function testGrouped()
    {
        $this->runShellFormatter(
            array(
                'use_group_as_prefix' => true
            ),
            'simple_grouped.sh'
        );
    }

    protected function runShellFormatter(array $parameters = array(), $fixture_file = "simple.sh")
    {
        $temp_file = tempnam(sys_get_temp_dir(), 'environaut');
        $this->assertTrue($temp_file !== false, 'Could not create a temporary file');

        $formatter = new ShellSettingsWriter();
        $parameters = array_merge($parameters, array(
            'location' => $temp_file
        ));
        $formatter->setParameters(new Parameters($parameters));

        $output = $formatter->format($this->getDefaultReport());
        $this->assertNotContains('FAILED', $output);

        $this->assertFileEquals(__DIR__ . '/Fixtures/' . $fixture_file, $temp_file);

        unlink($temp_file);
    }

    /**
     * @return \Environaut\Report\Report
     */
    protected function getDefaultReport()
    {
        $report = new Report();

        $result = new Result();
        $result->addSetting(new Setting('name', 'value'));
        $result->addSetting(new Setting('foo', 'bar'));
        $result->addSetting(new Setting('custom', true, 'group'));
        $result->setStatus(Result::SUCCESS);

        $report->addResult($result);

        return $report;
    }
}
