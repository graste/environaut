<?php

namespace Environaut\Tests\Config\Reader\Dom;

use Environaut\Config\Reader\Dom\DomDocument;
use Environaut\Tests\BaseTestCase;

class DomDocumentTest extends BaseTestCase
{
    public function testExampleConfigReading()
    {
        $schema_file = realpath(__DIR__ . '/../../../../../../src/Environaut/Config/Reader/Schema/environaut.xsd');
        $this->assertTrue(is_readable($schema_file), 'Schema file ' . $schema_file . ' is not readable.');

        $doc = new DomDocument();
        $doc->load(__DIR__ . '/Fixtures/example.xml', LIBXML_NOCDATA);
        $doc->xinclude(LIBXML_NOCDATA);
        $doc->schemaValidate($schema_file);

        $this->assertSame('example with included checks', $doc->getElementValue('name'));
        $expected = <<<EOT

omgomgomg
        multiline
foobar
        stuff

EOT;
        $this->assertSame($expected, $doc->getElementValue('introduction'));
        $this->assertSame(null, $doc->getElementValue('description'));
        $this->assertSame('ec', $doc->getDefaultNamespacePrefix());
        $this->assertSame('http://mivesto.de/environaut/config/1.0', $doc->getDefaultNamespaceUri());

        $report_node = $doc->getElement('report');
        $this->assertNotNull($report_node);
        $this->assertTrue($report_node->hasParameters());
        $this->assertSame('Custom\Report', $report_node->getAttributeValue('class'));
        $this->assertSame(array('class' => 'Custom\Report'), $report_node->getAttributes());
        $this->assertSame(array('test' => 'hahaha'), $report_node->getParameters());

        $runner_node = $doc->getElement('runner');
        $this->assertNotNull($runner_node);
        $this->assertTrue($runner_node->hasParameters());
        $this->assertSame('Custom\Runner', $runner_node->getAttributeValue('class'));
        $this->assertSame(array('class' => 'Custom\Runner'), $runner_node->getAttributes());
        $this->assertSame(
            array(
                'foo' => array(
                    array(
                        'deeply_nested' => true,
                        'nested' => 'true',
                        'non_literalized' => 'on',
                        'literalized' => false,
                        'whitespace_preserved' => ' haha '
                    )
                )
            ),
            $runner_node->getParameters()
        );

        $keywords_expected = array('asdf', 'blub', 'foo');
        $keywords_actual = array();

        $keywords_node = $doc->getElement('keywords');
        foreach ($keywords_node as $keyword) {
            $keywords_actual[] = (string) $keyword;
        }
        $this->assertSame($keywords_expected, $keywords_actual);

        $keywords_actual = array();
        foreach ($keywords_node->getChildren('keyword') as $keyword) {
            $keywords_actual[] = (string) $keyword;
        }
        $this->assertSame($keywords_expected, $keywords_actual);

        $checks_node = $doc->getElement('checks');
        $this->assertNotNull($checks_node);
        $this->assertTrue($checks_node->hasChildren('check'));

        $this->assertSame(8, $checks_node->countChildren('check'));

        $checks = $checks_node->getChildren('check');

        $this->assertSame('Blah\Check', $checks->item(0)->getAttributeValue('class'));
        $this->assertSame('group', $checks->item(0)->getAttributeValue('group'));
        $this->assertSame(true, $checks->item(0)->hasParameters());

        $this->assertSame('default value', $checks->item(0)->getAttributeValue('hahahaha', 'default value'));
    }
}
