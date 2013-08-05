<?php

namespace Environaut\Tests\Export\Formatter;

use Environaut\Config\Parameters;
use Environaut\Export\Formatter\PhpSettingsWriter;
use Environaut\Report\Report;
use Environaut\Report\Results\Result;
use Environaut\Report\Results\Settings\Setting;
use Environaut\Tests\BaseTestCase;

class PhpSettingsWriterTest extends BaseTestCase
{
    public function testConstruct()
    {
        $temp_file = tempnam(sys_get_temp_dir(), 'environaut');
        $this->assertTrue($temp_file !== false, 'Could not create a temporary file');

        $formatter = new PhpSettingsWriter();
        $formatter->setParameters(new Parameters(array('location' => $temp_file)));

        // DO NOT REMOVE THE TRAILING WHITESPACE IN simple.php after the group names arrow

        $output = $formatter->format($this->getDefaultReport());
        $this->assertNotContains('FAILED', $output);

        $this->assertFileEquals(__DIR__ . '/Fixtures/simple.php', $temp_file);

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
