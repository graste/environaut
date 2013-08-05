<?php

namespace Environaut\Tests\Config\Reader\Dom;

use Environaut\Config\Reader\Dom\DomElement;
use Environaut\Tests\BaseTestCase;

class DomElementTest extends BaseTestCase
{
    public function testLiteralize()
    {
        $this->assertEquals('asdf', DomElement::literalize('asdf'));
        $this->assertEquals(true, DomElement::literalize('true'));
        $this->assertEquals(false, DomElement::literalize('false'));
        $this->assertEquals(true, DomElement::literalize('trUe'));
        $this->assertEquals(false, DomElement::literalize('faLSe'));
        $this->assertEquals(true, DomElement::literalize('on'));
        $this->assertEquals(false, DomElement::literalize('off'));
        $this->assertEquals(true, DomElement::literalize('yes'));
        $this->assertEquals(false, DomElement::literalize('NO'));
        $this->assertEquals(null, DomElement::literalize(''));
        $this->assertEquals('null', DomElement::literalize('null'));
        $this->assertEquals(
            '<parameter name="foo">true</parameter>',
            DomElement::literalize('<parameter name="foo">true</parameter>')
        );
    }
}
