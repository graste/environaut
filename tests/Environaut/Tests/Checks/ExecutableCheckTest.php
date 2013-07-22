<?php

namespace Environaut\Tests\Checks;

use Environaut\Tests\BaseTestCase;
use Environaut\Tests\Checks\Fixtures\TestableExecutableCheck;

class ExecutableCheckTest extends BaseTestCase
{
    public function testConstruct()
    {
        $check = new \Environaut\Checks\ExecutableCheck('foo');

        $this->assertEquals('foo', $check->getName());
        $this->assertInstanceOf('\Environaut\Report\Results\IResult', $check->getResult());
        $this->assertEquals(null, $check->getCommand());
    }

    public function testDefaults()
    {
        $check = $this->runExecutableCheck(
            "\n",
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
        $this->assertCount(0, $settings, 'expected default group to be empty/nonexisting as "trololo" was the group name.');
        $settings = $check->getResult()->getSettingsAsArray('trololo');
        $this->assertCount(1, $settings, 'group "trololo" should contain the setting');
        $this->assertArrayHasKey('cmd.curl', $settings); // default setting name is "cmd.__NAME"
        $this->assertEquals('/usr/bin/curl', $settings['cmd.curl']); // default path prefix is "/usr/bin/"
    }

    public function testDefaultCommand()
    {
        $check = $this->runExecutableCheck(
            "\n",
            array(
                'setting' => 'command_curl',
                'default' => '/usr/sbin/some_wrapper'
            ),
            'trololo'
        );

        $settings = $check->getResult()->getSettingsAsArray('trololo');
        $this->assertArrayHasKey('command_curl', $settings);
        $this->assertEquals('/usr/sbin/some_wrapper', $settings['command_curl']);
    }

    public function testDefaultIsWorking()
    {
        $check = $this->runExecutableCheck(
            "\n",
            array(
                'setting' => 'command_curl',
                'default' => '/usr/sbin/some_wrapper'
            ),
            'trololo'
        );

        $settings = $check->getResult()->getSettingsAsArray('trololo');
        $this->assertArrayHasKey('command_curl', $settings);
        $this->assertEquals('/usr/sbin/some_wrapper', $settings['command_curl']);
    }

    public function testInputIsWorking()
    {
        $check = $this->runExecutableCheck(
            "/usr/sbin/trololo\n",
            array(),
            'trololo',
            'curl'
        );

        $settings = $check->getResult()->getSettingsAsArray('trololo');
        $this->assertArrayHasKey('cmd.curl', $settings);
        $this->assertEquals('/usr/sbin/trololo', $settings['cmd.curl']);
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
    protected function runExecutableCheck($input, array $params = array(), $group = 'default', $name = 'executable_check')
    {
        $check = new TestableExecutableCheck($name, $group, $params);
        $check->setInput($input);
        $check->run();
        return $check;
    }
}
