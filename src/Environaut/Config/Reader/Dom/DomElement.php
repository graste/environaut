<?php

namespace Environaut\Config\Reader\Dom;

/**
 * Custom \DOMElement implementation for Environaut to have some
 * convenience methods to get child nodes etc. Please be aware, that
 * by default these methods use the Environaut default namespace URI.
 *
 * @todo make namespace URI configurable for convenience methods
 */
class DomElement extends \DOMElement implements \IteratorAggregate
{
    /**
     * @var DomDocument
     */
    protected $ownerDocument;

    /**
     * Returns the nodeValue of the element.
     *
     * @return string nodeValue of the element
     */
    public function __toString()
    {
        return $this->getValue();
    }

    /**
     * Returns the nodeName of the element.
     *
     * @return string nodeName of the element
     */
    public function getName()
    {
        return $this->nodeName;
    }

    /**
     * Returns the nodeValue of the element.
     *
     * @return string nodeValue of the element
     */
    public function getValue()
    {
        return $this->nodeValue;
    }

    /**
     * Returns a \DOMNodeList of all child elements of the element.
     *
     * @return \DOMNodeList (Traversable) of all child elements
     */
    public function getIterator()
    {
        return $this->getChildNodes();
    }

    /**
     * Returns a \DOMNodeList of all child elements of the element.
     *
     * @return \DOMNodeList of all child elements
     */
    public function getChildNodes()
    {
        $prefix = $this->ownerDocument->getDefaultNamespacePrefix();

        if ($prefix) {
            return $this->ownerDocument->getXpath()->query(sprintf('child::%s:*', $prefix), $this);
        } else {
            return $this->ownerDocument->getXpath()->query('child::*', $this);
        }
    }

    /**
     * Returns the value of the given attribute or returns the given
     * default value if the attribute is missing.
     *
     * @param string $name name of attribute
     * @param mixed $default_value value to return if attribute is missing
     *
     * @return mixed string value of attribute or default_value if attribute is not found or empty
     */
    public function getAttributeValue($name, $default_value = null)
    {
        $value = parent::getAttribute($name);

        if ($value === '') {
            $value = $default_value;
        }

        return $value;
    }

    /**
     * Returns all attributes including their values for the element.
     *
     * @return array of attributes (name => value pairs)
     */
    public function getAttributes()
    {
        $attributes = array();

        foreach ($this->ownerDocument->getXpath()->query('@*', $this) as $attribute) {
            $attributes[$attribute->localName] = $attribute->nodeValue;
        }

        return $attributes;
    }

    /**
     * Returns all child elements of the given name or all children
     * of the element if name is not specified.
     *
     * @param string $name name of child elements to get
     *
     * @return \DOMNodeList of all children
     */
    public function get($name)
    {
        if (!$name) {
            return $this->getChildNodes();
        }

        return $this->getChildren($name);
    }

    /**
     * Determines whether there are children of the element
     * with the given name.
     *
     * @param string $name name of child element to check
     *
     * @return bool true if children of that name exist on the element
     */
    public function has($name)
    {
        return $this->hasChildren($name);
    }

    /**
     * Determines whether there's at least one child node with the given name.
     *
     * @param string $name name of child node
     *
     * @return bool true, if a first child with that name exists
     */
    public function hasChild($name)
    {
        return $this->getChild($name) !== null;
    }

    /**
     * Returns first child of the element with the given name.
     *
     * @param string $name name of element to get
     *
     * @return \DOMNode first child node with the given name
     */
    public function getChild($name)
    {
        $query = 'self::node()[count(child::*[local-name() = "%1$s" and namespace-uri() = "%2$s"]) = 1]/*' .
                '[local-name() = "%1$s" and namespace-uri() = "%2$s"]';

        $node = $this->ownerDocument->getXpath()->query(
            sprintf($query, $name, $this->ownerDocument->getDefaultNamespaceUri()),
            $this
        )->item(0);

        return $node;
    }

    /**
     * Determines whether there are children of the element
     * with the given name.
     *
     * @param string $name name of child element to check
     *
     * @return bool true if children of that name exist on the element
     */
    public function hasChildren($name)
    {
        return $this->countChildren($name) !== 0;
    }

    /**
     * Returns the count of the children with the given name.
     * Special handling for the following names is implemented:
     * - 'paramaters' returns count of all direct 'parameters' OR all 'parameters/parameter' child nodes
     * - 'formatters' returns count of all direct 'formatters' OR all 'formatters/formatter' child nodes
     *
     * @param string $name element name
     *
     * @return int number of child elements of the given name
     */
    public function countChildren($name)
    {
        $query = '';
        $singular_name = null;

        // special nodes of environaut config files where the surrounding plural node
        // may be omitted and the singular nodes be used directly (for less verbose xml)
        if (in_array($name, array('parameters', 'formatters'))) {

            if ($name === 'parameters') {
                $singular_name = 'parameter';
            } elseif ($name === 'formatters') {
                $singular_name = 'formatter';
            }

            $query = 'count(child::*[local-name() = "%2$s" and namespace-uri() = "%3$s"]) + ' .
                    'count(child::*[local-name() = "%1$s" and namespace-uri() = "%3$s"]/*' .
                    '[local-name() = "%2$s" and namespace-uri() = "%3$s"])';
        } elseif (empty($name)) {
            $query = 'count(child::*[namespace-uri() = "%3$s"])';
        } else {
            $query = 'count(child::*[local-name() = "%1$s" and namespace-uri() = "%3$s"])';
        }

        $count = (int) $this->ownerDocument->getXpath()->evaluate(
            sprintf($query, $name, $singular_name, $this->ownerDocument->getDefaultNamespaceUri()),
            $this
        );

        return $count;
    }

    /**
     * Returns all child elements of the given name. Special handling for the following names is implemented:
     * - 'paramaters' returns all direct 'parameters' OR all 'parameters/parameter' child nodes
     * - 'formatters' returns all direct 'formatters' OR all 'formatters/formatter' child nodes
     *
     * @param string $name name of child element
     *
     * @return \DOMNodeList of all child elements with the given name in the default namespace
     */
    public function getChildren($name)
    {
        $query = '';
        $singular_name = null;

        // special nodes of environaut config files where the surrounding plural node
        // may be omitted and the singular nodes be used directly (for less verbose xml)
        if (in_array($name, array('parameters', 'formatters'))) {

            if ($name === 'parameters') {
                $singular_name = 'parameter';
            } elseif ($name === 'formatters') {
                $singular_name = 'formatter';
            }

            $query = 'child::*[local-name() = "%2$s" and namespace-uri() = "%3$s"] | ' .
                    'child::*[local-name() = "%1$s" and namespace-uri() = "%3$s"]/*' .
                    '[local-name() = "%2$s" and namespace-uri() = "%3$s"]';
        } else {
            $query = 'child::*[local-name() = "%1$s" and namespace-uri() = "%3$s"]';
        }

        $nodes = $this->ownerDocument->getXpath()->query(
            sprintf($query, $name, $singular_name, $this->ownerDocument->getDefaultNamespaceUri()),
            $this
        );

        return $nodes;
    }

    /**
     * Determine whether there's a 'parameters' element as child of the element.
     *
     * @return boolean true if there is a 'parameters' element
     */
    public function hasParameters()
    {
        if ($this->ownerDocument->isEnvironautDocument()) {
            return $this->has('parameters');
        }

        return false;
    }

    /**
     * Returns all (nested) parameters of the element as an associative array.
     *
     * @param array $default array to be used as default
     * @param boolean $literalize whether or not to literalize values like 'true'/'false' by default
     *
     * @return array of associative nested parameters (numerical keys where a 'name' attribute is missing)
     */
    public function getParameters(array $default = array(), $literalize = true)
    {
        $offset = 0;

        if ($this->ownerDocument->isEnvironautDocument()) {
            $elements = $this->get('parameters');
            foreach ($elements as $element) {

                $name = null;
                if (!$element->hasAttribute('name')) {
                    $name = $offset++;
                    $default[$name] = null;
                } else {
                    $name = $element->getAttributeValue('name');
                }

                if ($element->hasParameters()) {
                    $default[$name] = isset($default[$name]) && is_array($default[$name]) ? $default[$name] : array();
                    $default[$name] = $element->getParameters($default[$name], $literalize);
                } else {
                    $default[$name] = $this->getLiteralValue($element);
                }
            }
        }

        return $default;
    }

    /**
     * Depending on the values of the 'literalize' and 'space' attributes of the element
     * the transformed or literal value of the value is returned
     *
     * @param \DOMNode $element element to get a value for
     *
     * @return mixed literalized and/or whitespace preserved value or null if value is empty
     */
    protected function getLiteralValue($element)
    {
        $value = $element->getValue();
        $trimmed_value = trim($value);

        $preserve_whitespace = $element->getAttributeValue('space', 'default') === 'preserve';
        $literalize_value = self::literalize($element->getAttributeValue('literalize')) !== false;

        if ($literalize_value) {
            if ($preserve_whitespace && ($trimmed_value === '' || $value !== $trimmed_value)) {
                $value = $value;
            } else {
                $value = self::literalize($trimmed_value);
            }
        } elseif (!$preserve_whitespace) {
            $value = $trimmed_value;
            if ($value === '') {
                $value = null;
            }
        }

        return $value;
    }

    /**
     * Returns the literalized value, that is:
     * - 'on|yes|true' will be boolean TRUE
     * - 'off|no|false' will be boolean FALSE
     * - '' will be NULL
     *
     * All other non-string values will be returned as is.
     *
     * @param mixed $value value to literalize
     *
     * @return mixed null or boolean value or the original value of it's not a string
     */
    public static function literalize($value)
    {
        if (!is_string($value)) {
            return $value;
        }

        $value = trim($value);
        if ($value == '') {
            return null;
        }

        $lc_value = strtolower($value);
        if ($lc_value === 'on' || $lc_value === 'yes' || $lc_value === 'true') {
            return true;
        } elseif ($lc_value === 'off' || $lc_value === 'no' || $lc_value === 'false') {
            return false;
        }

        return $value;
    }
}
