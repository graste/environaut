<?php

namespace Environaut\Tests\Command;

use Environaut\Tests\BaseTestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class CheckCommandTest extends BaseTestCase
{
    public function testExecuteFailsWithNonExistantConfigFile()
    {
        $this->setExpectedException('InvalidArgumentException');
        $this->executeCheckCommand('trololo-nonexistant.json');
    }

    public function testExecuteWorksWithEmptyConfigFile()
    {
        $text = $this->executeCheckCommand('empty.json');

        $this->assertContains('Environment Check', $text);
        $this->assertContains('Reading configuration from: ' . $this->getFixture('empty.json'), $text);
    }

    public function testVerboseExecute()
    {
        $text = $this->executeCheckCommand(
            'empty.json',
            array(
                '--verbose' => true
            )
        );

        $this->assertContains('No autoload_dir specified', $text);
        $this->assertContains('PHP Version', $text);
        $this->assertContains('Loaded php.ini File', $text);
    }

    public function testAutoloadDirOption()
    {
        $text = $this->executeCheckCommand(
            'empty.json',
            array(
                '--autoload-dir' => __DIR__,
                '--verbose' => true
            )
        );

        $this->assertContains('Classes will be autoloaded from "' . __DIR__ . '"', $text);
    }

    public function testNonExistingAutoloadDirOption()
    {
        $this->setExpectedException('InvalidArgumentException');
        $this->executeCheckCommand(
            'empty.json',
            array(
                '--autoload-dir' => __DIR__ . 'trololo-nonexistant'
            )
        );
    }

    public function testCustomConfigHandlerOption()
    {
        $this->assertTrue(class_exists('Fixtures\TestConfigHandler')); // autoloaded by bootstrap.php and composer

        $text = $this->executeCheckCommand(
            'empty.json',
            array(
                '--config-handler' => 'Fixtures\TestConfigHandler',
                '--verbose' => true,
            )
        );

        $this->assertContains('introductory text', $text);
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
        $application->add(new Fixtures\TestableCheckCommand()); // extends \Environaut\Command\CheckCommand but does not run checks

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
