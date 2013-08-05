<?php

namespace Environaut\Tests\Config\Reader;

use Environaut\Config\Reader\XmlConfigReader;
use Environaut\Tests\BaseTestCase;

class XmlConfigReaderTest extends BaseTestCase
{
    public function testReadSimpleConfig()
    {
        $this->setExpectedException('DomException');
        $reader = new XmlConfigReader();
        $reader->getConfigData(__DIR__ . '/Fixtures/wrong_classname.xml');
    }

    public function testReadXincludedConfig()
    {
        $reader = new XmlConfigReader();
        $config = $reader->getConfigData(__DIR__ . '/Fixtures/xinclude_checks.xml');

        $this->assertSame(8, $reader->getDocument()->getElementsByTagName('check')->length);

        $this->assertArrayHasKey('name', $config);
        $this->assertSame('simple check include', $config['name']);
        $this->assertArrayNotHasKey('description', $config);

        $this->assertArrayHasKey('keywords', $config);
        $this->assertSame(
            array('asdf', 'blub', 'foo'),
            $config['keywords']
        );

        $this->assertArrayHasKey('runner', $config);
        $this->assertSame(
            array('foo' => 'bar', '__class' => 'Custom\Runner'),
            $config['runner']
        );

        $this->assertArrayHasKey('report', $config);
        $this->assertSame(
            array('test' => 'hahaha', '__class' => 'Custom\Report'),
            $config['report']
        );

        $this->assertArrayHasKey('export', $config);
        $this->assertSame(
            array(
                'switch' => true,
                'formatters' => array(
                    array(
                        'location' => 'custom-location.txt',
                        '__class' => 'Custom\Formatter',
                    ),
                    array(
                        'location' => 'foo.xml',
                        'format' => 'xml',
                        'pretty' => true,
                    ),
                    array(
                        'location' => 'foo.json',
                        'format' => 'json',
                        'pretty' => 'true',
                        'groups' => array(
                            'default',
                            'custom'
                        ),
                    ),
                ),
                '__class' => 'Custom\Export'
            ),
            $config['export']
        );

        $checks = $config['checks'];
        $check = $checks[7];
        $this->assertSame('correct', $check['__name']);
        $this->assertSame(
            array(
                'setting' => 'key',
                'question' => '    question    ',
                'default' => true,
                'choices' => array(
                    'choice 1',
                    'choice 2',
                    'choice 3',
                ),
                '__name' => 'correct'
            ),
            $check
        );
    }
}
