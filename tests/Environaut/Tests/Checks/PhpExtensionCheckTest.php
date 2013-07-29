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

    public function testNestedVersionParameterFails()
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
                    'value' => '<=2.0'
                )
            )
        );

        $this->assertFalse($check->run());
    }

    public function testNestedNonMatchingVersionParameterFails()
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
                    'regex' => '|libXML hahaha Version => (?P<version>\d+.+?)\n|',
                    'value' => '>=2.0'
                )
            )
        );

        $this->assertFalse($check->run());
    }

    public function testNestedVersionParameterWithoutNamedCapturingGroupFails()
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

        $this->assertFalse($check->run());
    }

    public function testNestedVersionParameterWithMissingKeysThrows()
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
                    'vale' => '>=2.6.26',
                    'some' => 'more'
                )
            )
        );

        $this->setExpectedException('InvalidArgumentException');
        $check->run();
    }

    public function testRegexFails()
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
                'regex' => '|HAHAHAHA (Compiled )?Version => (\d+.+?)\n|',
            )
        );

        $this->assertFalse($check->run(), 'The regex should not have matched and thus the check should FALSE.');
    }

    public function testRegexWorks()
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
                'regex' => '|libXML (Compiled )?Version => (\d+.+?)\n|',
            )
        );

        $this->assertTrue($check->run());
    }

    public function testMultipleRegexWorks()
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
                'regex' => array(
                    '|libXML (Compiled )?Version => (\d+.+?)\n|',
                    '/libXML.+?Version => .+?\n/',
                    '#libxml#i',
                )
            )
        );

        $this->assertTrue($check->run());
    }

    public function testMultipleRegexWithOneFailingWorks()
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
                'regex' => array(
                    '/libXML.+?Version => .+?\n/',
                    '#trololo#', // should not be there .-)
                )
            )
        );

        $this->assertFalse($check->run());
    }

    public function testMultipleRegexFailsWhenRegexIsMissing()
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
                'regex' => array(
                    '|libXML (Compiled )?Version => (\d+.+?)\n|',
                    '/libXML.+?Version => .+?\n/',
                    true,
                )
            )
        );

        $this->setExpectedException('InvalidArgumentException');
        $check->run();
    }
}
