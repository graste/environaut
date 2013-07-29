<?php

namespace Environaut\Tests\Checks;

use Environaut\Report\Results\Messages\Message;
use Environaut\Tests\BaseTestCase;
use Environaut\Tests\Checks\Fixtures\TestableConfigurator;

class PhpSettingCheckTest extends BaseTestCase
{
    public function testConstruct()
    {
        $check = new \Environaut\Checks\PhpSettingCheck();

        $this->assertInstanceOf('\Environaut\Report\Results\IResult', $check->getResult());
        $this->assertEquals(null, $check->getCommand());
    }

    public function testGetIntegerValue()
    {
        $check = new \Environaut\Checks\PhpSettingCheck();

        $actual = $check::getIntegerValue('>=1M');
        $this->assertEquals($actual, 1024*1024);

        $actual = $check::getIntegerValue(' <1M ');
        $this->assertEquals($actual, 1024*1024);

        $actual = $check::getIntegerValue('1M');
        $this->assertEquals($actual, 1024*1024);

        $actual = $check::getIntegerValue('2M');
        $this->assertEquals($actual, 2*1024*1024);

        $actual = $check::getIntegerValue('256M');
        $this->assertEquals($actual, 256*1024*1024);

        $actual = $check::getIntegerValue('1G');
        $this->assertEquals($actual, 1024*1024*1024);

        $actual = $check::getIntegerValue('500K');
        $this->assertEquals($actual, 500*1024);

        // PHP treats string with decimal numbers on integer values in php.ini as integer values
        // (it's okay, just read that again)
        $actual = $check::getIntegerValue('2.75M');
        $this->assertEquals($actual, 2*1024*1024);

        $actual = $check::getIntegerValue(2.75);
        $this->assertEquals($actual, 2);

        $actual = $check::getIntegerValue("1e3");
        $this->assertEquals($actual, 1);
    }

    public function testGetOperator()
    {
        $check = new \Environaut\Checks\PhpSettingCheck();

        $actual = $check::getOperator('1M');
        $this->assertEquals($actual, '>=');

        $actual = $check::getOperator('>2M');
        $this->assertEquals($actual, '>');

        $actual = $check::getOperator('=256M');
        $this->assertEquals($actual, '=');

        $actual = $check::getOperator('<=1G');
        $this->assertEquals($actual, '<=');

        $actual = $check::getOperator('!=500K');
        $this->assertEquals($actual, '!=');
    }

    public function testCompareIntegers()
    {
        $check = new \Environaut\Checks\PhpSettingCheck();

        $this->assertTrue($check::compareIntegers('0', '0'));
        $this->assertTrue($check::compareIntegers('1', '1'));
        $this->assertTrue($check::compareIntegers(-1, '<=1'));
        $this->assertTrue($check::compareIntegers(300, '>=299'));
        $this->assertTrue($check::compareIntegers('256M', '256M'));
        $this->assertTrue($check::compareIntegers('256M', '=256M'));
        $this->assertTrue($check::compareIntegers('256M', '>=256M'));
        $this->assertTrue($check::compareIntegers('256M', ' >=256M '));
        $this->assertTrue($check::compareIntegers('1K', '>1000'));
        $this->assertTrue($check::compareIntegers('1023', '<=1K'));
        $this->assertTrue($check::compareIntegers('1K', '<=1024'));
        $this->assertTrue($check::compareIntegers('1K', '<1025'));
        $this->assertTrue($check::compareIntegers('1G', '>=0'));
        $this->assertTrue($check::compareIntegers('2G', '=2048M'));
        $this->assertTrue($check::compareIntegers('1025', '!=1K'));
        $this->assertTrue($check::compareIntegers('2047M', '<2G'));

        $this->assertTrue($check::compareIntegers('2e3', '>=2'));
    }
}
