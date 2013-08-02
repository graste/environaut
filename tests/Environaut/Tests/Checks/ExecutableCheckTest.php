<?php

namespace Environaut\Tests\Checks;

use Environaut\Config\Parameters;
use Environaut\Checks\ExecutableCheck;
use Environaut\Tests\BaseTestCase;
use Environaut\Tests\Checks\Fixtures\TestableExecutableCheck;

class ExecutableCheckTest extends BaseTestCase
{
    public function testConstruct()
    {
        $check = new ExecutableCheck();
        $check->setName('foo');

        $this->assertEquals('foo', $check->getName());
        $this->assertInstanceOf('\Environaut\Report\Results\IResult', $check->getResult());
        $this->assertInstanceOf('\Environaut\Config\Parameters', $check->getParameters());
        $this->assertEquals(null, $check->getCommand());
    }

    public function testEmptySettingThrows()
    {
        $this->setExpectedException('InvalidArgumentException');
        $this->runExecutableCheck(
            PHP_EOL,
            array(
                'setting' => ''
            ),
            'trololo',
            'curl'
        );
    }

    public function testDefaults()
    {
        $check = $this->runExecutableCheck(
            PHP_EOL,
            array(
            ),
            'trololo',
            'curl'
        );

        $this->assertEquals('trololo', $check->getGroup());
        $this->assertContains('Path to the executable "curl"', $check->getOutput());
        $this->assertCount(1, $check->getResult()->getMessages());

        $settings = $check->getResult()->getSettingsAsArray();
        $this->assertCount(1, $settings, 'expected all settings when group is not specified');
        $settings = $check->getResult()->getSettingsAsArray('default');
        $this->assertCount(0, $settings, 'expected default group to be empty as "trololo" was the group name.');
        $settings = $check->getResult()->getSettingsAsArray('trololo');
        $this->assertCount(1, $settings, 'group "trololo" should contain the setting');
        $this->assertSame('cmd.curl', $settings[0]['name']); // default setting name is "cmd.__NAME"
        $this->assertEquals('/usr/bin/curl', $settings[0]['value']); // default path prefix is "/usr/bin/"
    }

    public function testDefaultCommand()
    {
        $check = $this->runExecutableCheck(
            PHP_EOL,
            array(
                'setting' => 'command_curl',
                'default' => '/usr/sbin/some_wrapper'
            ),
            'trololo'
        );

        $settings = $check->getResult()->getSettingsAsArray('trololo');
        $this->assertSame('command_curl', $settings[0]['name']);
        $this->assertEquals('/usr/sbin/some_wrapper', $settings[0]['value']);
    }

    public function testDefaultIsWorking()
    {
        $check = $this->runExecutableCheck(
            PHP_EOL,
            array(
                'setting' => 'command_curl',
                'default' => '/usr/sbin/some_wrapper'
            ),
            'trololo'
        );

        $settings = $check->getResult()->getSettingsAsArray('trololo');
        $this->assertSame('command_curl', $settings[0]['name']);
        $this->assertEquals('/usr/sbin/some_wrapper', $settings[0]['value']);
    }

    public function testInputIsWorking()
    {
        $check = $this->runExecutableCheck(
            "/usr/sbin/trololo" . PHP_EOL,
            array(),
            'trololo',
            'curl'
        );

        $settings = $check->getResult()->getSettingsAsArray('trololo');
        $this->assertSame('cmd.curl', $settings[0]['name']);
        $this->assertEquals('/usr/sbin/trololo', $settings[0]['value']);
    }

    /**
     * Runs an TestableExecutableCheck instance with the given input
     * and parameters and returns the instance afterwards.
     *
     * @param string $input
     * @param array $params
     * @param string $group
     * @param string $name
     *
     * @return \Environaut\Tests\Checks\Fixtures\TestableExecutableCheck
     */
    protected function runExecutableCheck(
        $input,
        array $params = array(),
        $group = 'default',
        $name = 'executable_check'
    ) {
        $check = new TestableExecutableCheck();
        $check->setName($name);
        $check->setGroup($group);
        $check->setParameters(new Parameters($params));
        $check->setInput($input);
        $check->run();

        return $check;
    }
}
