<?php

namespace Environaut\Config\Reader\Dom;

/**
 * Custom \DOMAttrib implementation with convenience methods to get attribute values.
 */
class DomAttribute extends \DOMAttr
{
    public function __toString()
    {
        return $this->getValue();
    }

    public function getValue()
    {
        return $this->nodeValue;
    }
}
