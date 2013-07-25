<?php

namespace Environaut\Tests\Config\Reader;

use Environaut\Tests\BaseTestCase;

class XmlSchemaTest extends BaseTestCase
{
    public function testValidateSimpleConfig()
    {
        $this->assertTrue($this->validateConfig('simple.xml'));
    }

    public function testValidateExtensiveConfig()
    {
        $this->assertTrue($this->validateConfig('extensive.xml'));
    }

    public function testValidateXincludeConfig()
    {
        $this->assertTrue(
            $this->validateConfig('xinclude_checks.xml'),
            'XML file does not validate after XInclude resolution'
        );
    }

    public function testWrongClassnameConfig()
    {
        $this->assertFalse($this->validateConfig('wrong_classname.xml'));
    }

    protected function validateConfig($filename = 'simple.xml')
    {
        $xml_file = realpath(__DIR__ . '/Fixtures/' . $filename);
        $this->assertTrue(is_readable($xml_file), 'XML file ' . $xml_file . ' is not readable.');

        $schema_file = realpath(__DIR__ . '/../../../../../src/Environaut/Config/Reader/Schema/environaut.xsd');
        $this->assertTrue(is_readable($schema_file), 'Schema file ' . $schema_file . ' is not readable.');

        libxml_use_internal_errors(true);

        $dom = new \DOMDocument('1.0', 'UTF-8');
        $this->assertTrue($dom->load($xml_file, LIBXML_NOCDATA), 'Could not load XML file ' . $xml_file . ' - Error was: ' . print_r(libxml_get_errors(), true));

        $this->assertNotEquals(-1, $dom->xinclude(), 'XInclude resolution failed. Error was: ' . print_r(libxml_get_errors(), true));

        $valid = $dom->schemaValidate($schema_file);

        libxml_use_internal_errors(false);

        return $valid;
    }
}
