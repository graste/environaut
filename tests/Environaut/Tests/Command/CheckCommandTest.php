<?php

namespace Environaut\Tests\Command;

use Environaut\Tests\BaseTestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class CheckCommandTest extends BaseTestCase
{
    public function testExecuteFailsWithoutExistingConfigFile()
    {
        $this->setExpectedException('InvalidArgumentException');
        $this->executeCheckCommand('trololo');
    }

    public function testExecuteWorksWithEmptyConfigFile()
    {
        $text = $this->executeCheckCommand('empty.json');

        $this->assertRegExp('/Environment Check/', $text);
        $this->assertRegExp('/Report follows:/', $text);
        $this->assertRegExp('/Config follows:/', $text);
        $this->assertRegExp('/Reading configuration from: ' . preg_quote($this->getFixture('empty.json'), '/') . '/', $text);
    }

    public function testVerboseExecute()
    {
        $text = $this->executeCheckCommand('empty.json', array(
            '--verbose' => true
        ));

        $this->assertRegExp('/No autoload_dir specified/', $text);
        $this->assertRegExp('/PHP Version/', $text);
        $this->assertRegExp('/Loaded php\.ini File/', $text);
    }

    public function testAutoloadDirOption()
    {
        $text = $this->executeCheckCommand('empty.json', array(
            '--autoload_dir' => __DIR__,
            '--verbose' => true
        ));

        $this->assertRegExp('/Classes will be autoloaded from "' . preg_quote(__DIR__, '/') . '"/', $text);
    }

    public function testNonExistingAutoloadDirOption()
    {
        $this->setExpectedException('InvalidArgumentException');
        $this->executeCheckCommand('empty.json', array(
            '--autoload_dir' => __DIR__ . 'trololo'
        ));
    }

    public function testCustomConfigHandlerOption()
    {
        $this->assertTrue(class_exists('Fixtures\TestConfigHandler')); // autoloaded by bootstrap.php and composer

        $text = $this->executeCheckCommand('empty.json', array(
            '--config_handler' => 'Fixtures\TestConfigHandler',
            '--verbose' => true,
        ));

        $this->assertRegExp('/introductory text/', $text);
    }

    /**
     * Executes CheckCommand with given config file and options.
     *
     * @param string $filename name of file in Fixtures folder
     * @param array $options CLI options
     *
     * @return string output
     */
    protected function executeCheckCommand($filename, array $options = array())
    {
        $application = new Application();
        $application->add(new \Environaut\Command\CheckCommand());

        $command = $application->find('check');

        $tester = new CommandTester($command);

        $tester->execute(
            array_merge(
                array(
                    'command' => $command->getName(),
                    '--config' => $this->getFixture($filename),
                ),
                $options
            )
        );

        return $tester->getDisplay();
    }

    protected function getFixture($filename)
    {
        return __DIR__ . '/Fixtures/' . $filename;
    }
}
