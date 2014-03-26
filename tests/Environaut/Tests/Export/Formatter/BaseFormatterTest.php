<?php

namespace Environaut\Tests\Export\Formatter;

use Environaut\Export\Formatter\BaseFormatter;
use Environaut\Tests\BaseTestCase;

class BaseFormatterTest extends BaseTestCase
{
    public function testVksprintfNormalArguments()
    {
        $tests = array(
            array(
                "input" => 'Nothing to do here!',
                "arguments" => array(
                    'to do' => 'nothing'
                ),
                "expected" => 'Nothing to do here!'
            ),
            array(
                "input" => '%s %s',
                "arguments" => array(
                    "blah" => "blub",
                    "foo" => "bar"
                ),
                "expected" => 'blub bar'
            ),
            array(
                "input" => '%04d %s',
                "arguments" => array(
                    "blah" => 123,
                    "foo" => "bar"
                ),
                "expected" => '0123 bar'
            ),
            array(
                "input" => 'The %2$s contains %1$05d monkeys',
                "arguments" => array(
                    'one' => 6,
                    'two' => 'tree'
                ),
                "expected" => 'The tree contains 00006 monkeys'
            ),
            array(
                "input" => 'The %2$s contains %1$04d monkeys',
                "arguments" => array(
                    5,
                    'tree'
                ),
                "expected" => 'The tree contains 0005 monkeys'
            ),
            array(
                "input" => 'The %2$s contains %1$01d monkeys',
                "arguments" => array(
                    5 => 7,
                    3 => 'house'
                ),
                "expected" => 'The house contains 7 monkeys'
            ),
            array(
                "input" => ">>>%'#-10s<<<",
                "arguments" => array(
                    "foo bar"
                ),
                "expected" => '>>>foo bar###<<<'
            ),
            array(
                "input" => ">>>%'#-10.10s<<<",
                "arguments" => array(
                    "foo bar is TOOLONG"
                ),
                "expected" => '>>>foo bar is<<<'
            ),
            array(
                "input" => '%1$s %1$\'#10s %1$s!',
                "arguments" => array(
                    'badger'
                ),
                "expected" => 'badger ####badger badger!'
            ),
            array(
                "input" => '%1$s %1$\'#10s %1$s!',
                "arguments" => array(
                    'badger',
                    'foo' => 'bar'
                ),
                "expected" => 'badger ####badger badger!'
            ),
            array(
                "input" => '',
                "arguments" => array(
                ),
                "expected" => ''
            ),
        );

        foreach ($tests as $test) {
            $this->assertEquals($test["expected"], BaseFormatter::vksprintf($test["input"], $test["arguments"]));
        }
    }

    public function testVksprintfNamedArguments()
    {
        $tests = array(
            array(
                "input" => 'Some %foo$s parameter',
                "arguments" => array(
                    "foo" => "named"
                ),
                "expected" => 'Some named parameter'
            ),
            array(
                "input" => 'Some %formatted$\'#-10s and positional %1$s and normal %s parameter',
                "arguments" => array(
                    "formatted" => "value"
                ),
                "expected" => 'Some value##### and positional value and normal value parameter'
            ),
            array(
                "input" => '%param$s must be between %min$03d and %max$03d.',
                "arguments" => array(
                    'min' => 3,
                    'max' => 99,
                    'param' => 'Value'
                ),
                "expected" => 'Value must be between 003 and 099.'
            ),
        );

        foreach ($tests as $test) {
            $this->assertEquals($test["expected"], BaseFormatter::vksprintf($test["input"], $test["arguments"]));
        }
    }

    public function testVksprintfInvalidInput()
    {
        $this->setExpectedException('RuntimeException');
        BaseFormatter::vksprintf(array('asdf','qwer'), array());
    }
}
