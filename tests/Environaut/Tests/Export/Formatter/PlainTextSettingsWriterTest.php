<?php

namespace Environaut\Tests\Export\Formatter;

use Environaut\Config\Parameters;
use Environaut\Export\Formatter\PlainTextSettingsWriter;
use Environaut\Report\Report;
use Environaut\Report\Results\Result;
use Environaut\Report\Results\Settings\Setting;
use Environaut\Tests\BaseTestCase;
use PHPUnit_Framework_ExpectationFailedException;

class PlainTextSettingsWriterTest extends BaseTestCase
{
    protected $fixture_file;

    protected $temp_file;

    public function setUp()
    {
        $fixture_file = sprintf(
            '%s/Fixtures/PlainText/%s',
            __DIR__,
            ltrim(
                strtolower(
                    preg_replace(
                        '/[A-Z]/',
                        '_$0',
                        str_replace('test', '', $this->getName())
                    )
                ),
                '_'
            )
        );

        if (is_readable($fixture_file)) {
            $this->fixture_file = $fixture_file;
        }

        $temp_file = tempnam(sys_get_temp_dir(), 'environaut');
        if ($temp_file !== false) {
            $this->temp_file = $temp_file;
        }
    }

    public function tearDown()
    {
        $this->fixture_file = null;

        if ($this->temp_file !== false) {
            //unlink($this->temp_file);
        }
    }

    public function testSingleWithoutGroupPath()
    {
        $formatter = new PlainTextSettingsWriter();
        $formatter->setParameters(
            new Parameters(
                array(
                    'location' => $this->temp_file,
                    'embed_group_path' => false,
                    'template' => '%environment$s'
                )
            )
        );

        $output = $formatter->format($this->getSingleSettingReport());
        $this->assertNotContains('FAILED', $output);

        $this->assertFileEquals($this->fixture_file, $this->temp_file);
    }

    public function testSingleWithGroupPath()
    {
        $formatter = new PlainTextSettingsWriter();
        $formatter->setParameters(
            new Parameters(
                array(
                    'location' => $this->temp_file,
                    'embed_group_path' => true,
                    'template' => '%core_settings.environment$s'
                )
            )
        );

        $output = $formatter->format($this->getSingleSettingReport());
        $this->assertNotContains('FAILED', $output);

        $this->assertFileEquals($this->fixture_file, $this->temp_file);
    }

    public function testMultipleWithoutGroupPath()
    {
        $formatter = new PlainTextSettingsWriter();
        $formatter->setParameters(
            new Parameters(
                array(
                    'location' => $this->temp_file,
                    'embed_group_path' => false,
                    'template' => <<<EOL
%environment\$s
%foo\$s
%bar\$s
EOL
                )
            )
        );

        $output = $formatter->format($this->getMultiSettingReport());
        $this->assertNotContains('FAILED', $output);

        $this->assertFileEquals($this->fixture_file, $this->temp_file);
    }

    public function testMultipleWithGroupPath()
    {
        $formatter = new PlainTextSettingsWriter();
        $formatter->setParameters(
            new Parameters(
                array(
                    'location' => $this->temp_file,
                    'embed_group_path' => true,
                    'template' => <<<EOL
%core_settings.environment\$s
%core_settings.foo\$s
%core_settings.bar\$s
EOL
                )
            )
        );

        $output = $formatter->format($this->getMultiSettingReport());
        $this->assertNotContains('FAILED', $output);

        $this->assertFileEquals($this->fixture_file, $this->temp_file);
    }

    /**
     * @return \Environaut\Report\Report
     */
    protected function getSingleSettingReport()
    {
        $report = new Report();

        $result = new Result();
        $result->addSetting(new Setting('environment', 'testing-vagrant', 'core_settings'));
        $result->setStatus(Result::SUCCESS);

        $report->addResult($result);

        return $report;
    }

    protected function getMultiSettingReport()
    {
        $report = new Report();

        $result = new Result();
        $result->addSetting(new Setting('environment', 'testing-vagrant', 'core_settings'));
        $result->addSetting(new Setting('foo', 'FOOOO', 'core_settings'));
        $result->addSetting(new Setting('bar', 'BAAAR', 'core_settings'));
        $result->setStatus(Result::SUCCESS);

        $report->addResult($result);

        return $report;
    }
}
