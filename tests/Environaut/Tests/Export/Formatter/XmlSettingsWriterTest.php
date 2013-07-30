<?php

namespace Environaut\Tests\Export\Formatter;

use Environaut\Tests\BaseTestCase;

class XmlSettingsWriterTest extends BaseTestCase
{
    public function testConstruct()
    {
        $temp_file = tempnam(sys_get_temp_dir(), 'environaut');
        $this->assertTrue($temp_file !== false, 'Could not create a temporary file');
        $formatter = new \Environaut\Export\Formatter\XmlSettingsWriter();
        $formatter->setOptions($this->mergeDefaultOptions(array('location' => $temp_file)));

        $formatter->format($this->getDefaultReport());

        $this->assertXmlFileEqualsXmlFile(__DIR__ . '/Fixtures/simple.xml', $temp_file);

        unlink($temp_file);
    }

    protected function getDefaultReport()
    {
        $report = new \Environaut\Report\Report();
        $result = new \Environaut\Report\Results\Result();
        $result->addSetting(new \Environaut\Report\Results\Settings\Setting('name', 'value'));
        $result->addSetting(new \Environaut\Report\Results\Settings\Setting('foo', 'bar'));
        $result->addSetting(new \Environaut\Report\Results\Settings\Setting('custom', true, 'group'));
        $report->addResult($result);
        return $report;
    }

    protected function mergeDefaultOptions(array $new = array())
    {
        $defaults = array(
            'file_template' => '<?xml version="1.0" encoding="UTF-8"?><config>%group_template$s</config>',
            'group_template' => '<settings prefix="%group_name$s.">%setting_template$s</settings>',
            'setting_template' => '<setting name="%setting_name$s">%setting_value$s</setting>',
        );

        return array_merge_recursive($defaults, $new);
    }
}
