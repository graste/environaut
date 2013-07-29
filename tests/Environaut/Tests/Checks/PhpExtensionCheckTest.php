<?php

namespace Environaut\Tests\Checks;

use Environaut\Tests\BaseTestCase;

class PhpExtensionCheckTest extends BaseTestCase
{
    public function testConstruct()
    {
        $check = new \Environaut\Checks\PhpExtensionCheck();

        $this->assertInstanceOf('\Environaut\Report\Results\IResult', $check->getResult());
        $this->assertEquals(null, $check->getCommand());
    }

    public function testEmptySettingOrNameThrows()
    {
        $check = new \Environaut\Checks\PhpExtensionCheck('');

        $this->setExpectedException('InvalidArgumentException');
        $check->run();
    }

    public function testNonExistingExtensionFails()
    {
        $check = new \Environaut\Checks\PhpExtensionCheck('hahaha.trololo');

        $this->assertFalse($check->run());
        $this->assertNotEmpty($check->getResult()->getMessages());
    }

    public function testNotLoadedWorks()
    {
        $extensions = get_loaded_extensions();
        $this->assertNotEmpty($extensions);
        $check = new \Environaut\Checks\PhpExtensionCheck(
            'asfdasdf',
            'default',
            array(
                'extension' => array_shift($extensions),
                'loaded' => false,
                'help' => 'Please disable the extension as...'
            )
        );

        $this->assertFalse($check->run());
    }

    public function testVersionWorks()
    {
        $extensions = get_loaded_extensions();

        // we're feeling lucky and look for fileinfo or json extension as those
        // usually have version information set (instead of other builtin extensions...)
        if (in_array('fileinfo', $extensions)) {
            $extension = 'fileinfo';
        } elseif (in_array('json', $extensions)) {
            $extension = 'json';
        } else {
            $this->markTestSkipped('There is no fileinfo or json extension available to run this test.');
            return;
        }

        $check = new \Environaut\Checks\PhpExtensionCheck(
            'asfdasdf',
            'default',
            array(
                'extension' => $extension,
                'loaded' => true,
                'version' => '>=1.0'
            )
        );

        $this->assertTrue($check->run());
    }

    public function testVersionFails()
    {
        $extensions = get_loaded_extensions();

        // we're feeling lucky and look for fileinfo or json extension as those
        // usually have version information set (instead of other builtin extensions...)
        if (in_array('fileinfo', $extensions)) {
            $extension = 'fileinfo';
        } elseif (in_array('json', $extensions)) {
            $extension = 'json';
        } else {
            $this->markTestSkipped('There is no fileinfo or json extension available to run this test.');
            return;
        }

        $check = new \Environaut\Checks\PhpExtensionCheck(
            'asfdasdf',
            'default',
            array(
                'extension' => $extension,
                'loaded' => true,
                'version' => '>1337'
            )
        );

        $this->assertFalse($check->run());
    }

    public function testNestedVersionParameterWorks()
    {
        $extensions = get_loaded_extensions();
        if (!in_array('libxml', $extensions)) {
            $this->markTestSkipped('There is no libxml extension available to run this test.');
            return;
        }

        $check = new \Environaut\Checks\PhpExtensionCheck(
            'asfdasdf',
            'default',
            array(
                'extension' => 'libxml',
                'version' => array(
                    'regex' => '|libXML (Compiled )?Version => (?P<version>\d+.+?)\n|',
                    'value' => '>=2.6.26'
                )
            )
        );

        $this->assertTrue($check->run());
    }

    public function testNestedVersionParameterThrows()
    {
        $extensions = get_loaded_extensions();
        if (!in_array('libxml', $extensions)) {
            $this->markTestSkipped('There is no libxml extension available to run this test.');
            return;
        }

        $check = new \Environaut\Checks\PhpExtensionCheck(
            'asfdasdf',
            'default',
            array(
                'extension' => 'libxml',
                'version' => array(
                    'regex' => '|libXML (Compiled )?Version => (\d+.+?)\n|',
                    'value' => '>=2.6.26'
                )
            )
        );

        $this->setExpectedException('InvalidArgumentException');
        $check->run();
    }
}
