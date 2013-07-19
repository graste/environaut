<?php

namespace Environaut\Tests;

use Environaut\Application;

use Symfony\Component\Console\Tester\ApplicationTester;

class ApplicationTest extends BaseTestCase
{
    public function testConstruct()
    {
        $application = new Application('1337');

        $this->assertEquals('1337', $application->getVersion());
    }

    public function testProfileRun()
    {
        $application = new Application('1337');
        $application->setAutoExit(false);
        $application->setCatchExceptions(false);

        $tester = new ApplicationTester($application);
        $tester->run(array('--profile' => true), array('decorated' => false));

        $output = $tester->getDisplay(true);

        $this->assertContains('Environaut version 1337', $output);
        $this->assertContains('Memory usage: ', $output);
    }
}
