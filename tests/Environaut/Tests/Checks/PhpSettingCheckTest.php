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

    public function testEmptySettingOrNameThrows()
    {
        $check = new \Environaut\Checks\PhpSettingCheck('');

        $this->setExpectedException('InvalidArgumentException');
        $check->run();
    }

    public function testNonExistingIniValueFails()
    {
        $check = new \Environaut\Checks\PhpSettingCheck(
            'hahaha.trololo',
            'default',
            array(
                'value' => 'omgomgomg'
            )
        );

        $this->assertFalse($check->run());
    }

    public function testNonExistingComparisonThrows()
    {
        $check = new \Environaut\Checks\PhpSettingCheck(
            'hahaha.trololo',
            'default',
            array(
                'comparison' => 'omgomgomg'
            )
        );

        $this->setExpectedException('InvalidArgumentException');
        $check->run();
    }

    public function testRegexComparisonSucceeds()
    {
        $ini_value = 'this is some arbitrary name';
        $this->assertTrue(false !== ini_set('session.name', $ini_value));
        $this->assertSame($ini_value, ini_get('session.name'));

        $check = new \Environaut\Checks\PhpSettingCheck(
            'session.name',
            'default',
            array(
                'value' => '/arbitrary/',
                'comparison' => 'regex'
            )
        );

        $this->assertTrue($check->run());
    }

    public function testRegexComparisonFails()
    {
        $ini_value = 'this is some arbitrary name';
        $this->assertTrue(false !== ini_set('session.name', $ini_value));
        $this->assertSame($ini_value, ini_get('session.name'));

        $check = new \Environaut\Checks\PhpSettingCheck(
            'session.name',
            'default',
            array(
                'value' => '/trololo/',
                'comparison' => 'regex'
            )
        );

        $this->assertFalse($check->run());
    }

    public function testNullComparisonWorks()
    {
        $this->assertTrue(false !== ini_set('error_log', null));
        $this->assertSame('', ini_get('error_log'));

        $check = new \Environaut\Checks\PhpSettingCheck('error_log', 'default', array('comparison' => 'null'));

        $this->assertTrue($check->run());
    }

    public function testNullComparisonFails()
    {
        $this->assertTrue(false !== ini_set('error_log', 'php_errors.log'));
        $this->assertSame('php_errors.log', ini_get('error_log'));

        $check = new \Environaut\Checks\PhpSettingCheck('error_log', 'default', array('comparison' => 'null'));

        $this->assertFalse($check->run());
    }

    public function testNotEmptyComparisonFails()
    {
        $this->assertTrue(false !== ini_set('error_log', null));
        $this->assertSame('', ini_get('error_log'));

        $check = new \Environaut\Checks\PhpSettingCheck('error_log', 'default', array('comparison' => 'notempty'));

        $this->assertFalse($check->run());
    }

    public function testValueComparisonWorks()
    {
        $this->assertTrue(false !== ini_set('error_log', 'php_errors.log'));
        $this->assertSame('php_errors.log', ini_get('error_log'));

        $check = new \Environaut\Checks\PhpSettingCheck('error_log', 'default', array('value' => 'php_errors.log'));

        $this->assertTrue($check->run());
    }

    public function testValueComparisonFails()
    {
        $this->assertTrue(false !== ini_set('error_log', 'php_errors.log'));
        $this->assertSame('php_errors.log', ini_get('error_log'));

        $check = new \Environaut\Checks\PhpSettingCheck('error_log', 'default', array('value' => 'php.log'));

        $this->assertFalse($check->run());
    }

    public function testBytesComparisonWorks()
    {
        $this->assertTrue(false !== ini_set('memory_limit', '128M'));
        $this->assertSame('128M', ini_get('memory_limit'));

        $check = new \Environaut\Checks\PhpSettingCheck(
            'memory_limit',
            'default',
            array(
                'value' => '>=128M',
                'comparison' => 'integer'
            )
        );

        $this->assertTrue($check->run());
    }

    public function testBytesComparisonFails()
    {
        $this->assertTrue(false !== ini_set('memory_limit', '128M'));
        $this->assertSame('128M', ini_get('memory_limit'));

        $check = new \Environaut\Checks\PhpSettingCheck(
            'memory_limit',
            'default',
            array(
                'value' => '=128000K',
                'comparison' => 'integer'
            )
        );

        $this->assertFalse($check->run());
    }

    public function testDecimalIntegerComparisonFails()
    {
        $this->assertTrue(false !== ini_set('memory_limit', '128.75M'));
        $this->assertSame('128.75M', ini_get('memory_limit'));

        $check = new \Environaut\Checks\PhpSettingCheck(
            'memory_limit',
            'default',
            array(
                'value' => '>128M',
                'comparison' => 'integer'
            )
        );

        $this->assertFalse($check->run());
    }

    public function testFloatAsStringValueComparisonWorks()
    {
        $this->assertTrue(false !== ini_set('date.default_latitude', '31.7667'));
        $this->assertSame('31.7667', ini_get('date.default_latitude'));

        $check = new \Environaut\Checks\PhpSettingCheck(
            'date.default_latitude',
            'default',
            array(
                'value' => '31.7667'
            )
        );

        $this->assertTrue($check->run());
    }

    public function testVersionComparisonWorks()
    {
        $this->assertTrue(version_compare('2.4', '2.5', '<='));
        $this->assertTrue(version_compare('31.7667', '31', '>='));
        $this->assertTrue(false !== ini_set('date.default_latitude', '31.7667'));
        $this->assertSame('31.7667', ini_get('date.default_latitude'));

        $check = new \Environaut\Checks\PhpSettingCheck(
            'date.default_latitude',
            'default',
            array(
                'value' => '>=31',
                'comparison' => 'version'
            )
        );

        $this->assertTrue($check->run());
    }

    public function testVersionComparisonFails()
    {
        $this->assertFalse(version_compare('31.7667', '31', '<='));
        $this->assertTrue(false !== ini_set('date.default_latitude', '31.7667'));
        $this->assertSame('31.7667', ini_get('date.default_latitude'));

        $check = new \Environaut\Checks\PhpSettingCheck(
            'date.default_latitude',
            'default',
            array(
                'value' => '<=31',
                'comparison' => 'version',
                'help' => 'some hints about what to do in case of failing check'
            )
        );

        $this->assertFalse($check->run());
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
