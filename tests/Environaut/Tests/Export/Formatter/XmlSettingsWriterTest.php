<?php

namespace Environaut\Tests\Export\Formatter;

use Environaut\Config\Parameters;
use Environaut\Export\Formatter\XmlSettingsWriter;
use Environaut\Report\Report;
use Environaut\Report\Results\Result;
use Environaut\Report\Results\Settings\Setting;
use Environaut\Tests\BaseTestCase;

class XmlSettingsWriterTest extends BaseTestCase
{
    public function testConstruct()
    {
        $temp_file = tempnam(sys_get_temp_dir(), 'environaut');
        $this->assertTrue($temp_file !== false, 'Could not create a temporary file');

        $formatter = new XmlSettingsWriter();
        $formatter->setParameters($this->mergeDefaultParameters(array('location' => $temp_file)));

        $output = $formatter->format($this->getDefaultReport());
        $this->assertNotContains('FAILED', $output);

        $this->assertXmlFileEqualsXmlFile(__DIR__ . '/Fixtures/simple.xml', $temp_file);

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

    /**
     * @param array $new parameters to be merged into the default ones
     *
     * @return \Environaut\Tests\Export\Formatter\Parameters
     */
    protected function mergeDefaultParameters(array $new = array())
    {
        $defaults = array(
            'file_template' => '<?xml version="1.0" encoding="UTF-8"?><config>%group_template$s</config>',
            'group_template' => '<settings prefix="%group_name$s.">%setting_template$s</settings>',
            'setting_template' => '<setting name="%setting_name$s">%setting_value$s</setting>',
        );

        return new Parameters(array_merge_recursive($defaults, $new));
    }
}
