<?php

namespace Environaut\Config\Reader\Dom;

/**
 * Environaut DOM document implementation with convenience wrapper methods
 * that by default use the Environaut configuration namespace for queries.
 */
class DomDocument extends \DOMDocument
{
    /**
     * Default namespace of Environaut configuration files.
     */
    const NAMESPACE_ENVIRONAUT_1_0 = 'http://mivesto.de/environaut/config/1.0';

    /**
     * Default namespace prefix for Environaut configuration files.
     */
    const NAMESPACE_PREFIX = 'ec';

    /**
     * @var \DOMXpath xpath instance for this document
     */
    protected $xpath = null;

    /**
     * @var string default URI used as namespace for the document
     */
    protected $default_namespace_uri;

    /**
     * @var string default prefix for the default namespace of this document
     */
    protected $default_namespace_prefix;

    /**
     * @var array map of DOM classes and our implementations of them
     */
    protected $class_map = array(
        'DOMAttr' => 'Environaut\Config\Reader\Dom\DomAttribute',
        'DOMDocument' => 'Environaut\Config\Reader\Dom\DomDocument',
        'DOMElement' => 'Environaut\Config\Reader\Dom\DomElement'
    );

    /**
     * Creates a new DomDocument instance.
     *
     * @param string $version
     * @param string $encoding
     */
    public function __construct($version = "1.0", $encoding = "UTF-8")
    {
        parent::__construct($version, $encoding);

        foreach ($this->class_map as $dom_class => $environaut_class) {
            $this->registerNodeClass($dom_class, $environaut_class);
        }

        $this->xpath = new \DOMXPath($this);
    }

    /**
     * Loads XML from a file and registers the Environaut configuration
     * namespace if the XML document is an Environaut configuration file.
     *
     * @param string $filename xml file to load
     * @param int $options libXml flags to use for loading (like LIBXML_NOCDATA etc.)
     *
     * @return bool true on success; false on error
     *
     * @throws \DOMException in case of errors loading the document from the given file
     */
    public function load($filename, $options = 0)
    {
        $user_error_handling = $this->enableErrorHandling();

        $success = parent::load($filename, $options);

        $this->handleErrors(
            'Loading the document failed. Details are:' . PHP_EOL . PHP_EOL,
            PHP_EOL . 'Please fix the mentioned errors.',
            $user_error_handling
        );

        $this->refreshXpath();

        return $success;
    }

    /**
     * Loads XML from a string and registers the Environaut configuration
     * namespace if the XML document is an Environaut configuration file.
     *
     * @param string $source xml string to load
     * @param int $options libXml flags to use for loading (like LIBXML_NOCDATA etc.)
     *
     * @return bool true on success; false on error
     *
     * @throws \DOMException in case of errors loading the document from the given string
     */
    public function loadXml($source, $options = 0)
    {
        $user_error_handling = $this->enableErrorHandling();

        $success = parent::loadXML($source, $options);

        $this->handleErrors(
            'Loading the document failed. Details are:' . PHP_EOL . PHP_EOL,
            PHP_EOL . 'Please fix the mentioned errors.',
            $user_error_handling
        );

        $this->refreshXpath();

        return $success;
    }

    /**
     * Validates the current document according to the given schema file.
     *
     * @param string $filename path to the schema file
     *
     * @return bool true on success
     *
     * @throws \DomException in case of validation errors or non-readable file
     */
    public function schemaValidate($filename)
    {
        if (!is_readable($filename)) {
            throw new \DOMException("Schema file is not readable: $filename");
        }

        $user_error_handling = $this->enableErrorHandling();

        $success = parent::schemaValidate($filename);

        $this->handleErrors(
            'Validating the document failed. Details are:' . PHP_EOL . PHP_EOL,
            PHP_EOL . 'Please fix the mentioned errors or use another schema file.',
            $user_error_handling
        );

        return $success;
    }

    /**
     * Validates the current document according to the given schema file content.
     *
     * @param string $source content of a schema file
     *
     * @return bool true on success
     *
     * @throws \DomException in case of validation errors or empty schema
     */
    public function schemaValidateSource($source)
    {
        if (empty($source)) {
            throw new \DOMException('Schema is empty.');
        }

        $user_error_handling = $this->enableErrorHandling();

        $success = parent::schemaValidateSource($source);

        $this->handleErrors(
            'Validating the document failed. Details are:' . PHP_EOL . PHP_EOL,
            PHP_EOL . 'Please fix the mentioned errors or use another schema.',
            $user_error_handling
        );

        return $success;
    }

    /**
     * Substitutes XIncludes present in the current document.
     *
     * @param int $options libXml flags to use for loading (like LIBXML_NOCDATA etc.)
     *
     * @return int number of XIncludes or false if no xincludes were found
     *
     * @throws \DOMException in case of errors xincluding content
     */
    public function xinclude($options = 0)
    {
        $user_error_handling = $this->enableErrorHandling();

        $number_of_xincludes = parent::xinclude($options);

        $this->handleErrors(
            'Resolving XInclude directives in the current document failed. Details are:' . PHP_EOL . PHP_EOL,
            PHP_EOL . 'Please fix the XInclude directives according to the mentioned errors.',
            $user_error_handling
        );

        return $number_of_xincludes;
    }

    /**
     * Returns the xpath instance that handles this document.
     *
     * @return \DOMXpath instance
     */
    public function getXpath()
    {
        return $this->xpath;
    }

    /**
     * Returns the nodeValue of the child element with the given name.
     *
     * @param string $element_name name of child element
     * @param \DomElement $reference_element reference element for child node iteration, default to documentElement
     *
     * @return mixed nodeValue or null if Environaut element with that name does not exist
     *
     * @throws \InvalidArgumentException if name is empty
     */
    public function getElementValue($element_name, $reference_element = null)
    {
        $element_name = trim($element_name);
        if (empty($element_name)) {
            throw new \InvalidArgumentException('Element name must not be empty.');
        }

        if (null === $reference_element) {
            $reference_element = $this->documentElement;
        }

        if ($this->isEnvironautDocument()) {
            foreach ($reference_element->childNodes as $node) {
                if ($node->nodeType == XML_ELEMENT_NODE &&
                    $node->localName == $element_name &&
                    $node->namespaceURI == $this->documentElement->namespaceURI
                ) {
                    return $node->nodeValue;
                }
            }
        }

        return null;
    }

    /**
     * Returns the DomElement with the given name.
     *
     * @param string $name of the element to get
     *
     * @return DomElement of that name or null if does not exist in the Environaut namespace
     */
    public function getElement($name)
    {
        if ($this->isEnvironautDocument()) {
            foreach ($this->documentElement->childNodes as $node) {
                if ($node->nodeType == XML_ELEMENT_NODE &&
                    $node->localName == $name &&
                    $node->namespaceURI == $this->documentElement->namespaceURI
                ) {
                    return $node;
                }
            }
        }

        return null;
    }

    /**
     * Set the default namespace that should be used when accessing elements via
     * convenience methods and bind it to the internal xpath instance.
     *
     * @param string $prefix default prefix to use
     * @param string $uri namespace URI
     */
    public function setDefaultNamespace($prefix = self::NAMESPACE_PREFIX, $uri = self::NAMESPACE_ENVIRONAUT_1_0)
    {
        $this->default_namespace_uri = $uri;
        $this->default_namespace_prefix = $prefix;

        $this->xpath->registerNamespace($prefix, $uri);
    }

    /**
     * Returns the default namespace URI used by Environaut.
     *
     * @return string default namespace URI
     */
    public function getDefaultNamespaceUri()
    {
        return $this->default_namespace_uri;
    }

    /**
     * Returns the default namespace prefix used by Environaut
     *
     * @return string default namespace prefix
     */
    public function getDefaultNamespacePrefix()
    {
        return $this->default_namespace_prefix;
    }

    /**
     * Returns whether the current document is an Environaut configuration file.
     *
     * @return bool true if it's an Environaut document; false otherwise
     */
    public function isEnvironautDocument()
    {
        return self::isEnvironautConfigurationDocument($this);
    }

    /**
     * Registers the default Environaut namespace on the internal xpath
     * instance of the given DomDocument instance.
     *
     * @param DOMDocument $doc document instance
     */
    public static function registerEnvironautNamespace(DOMDocument $doc)
    {
        $doc->getXpath()->registerNamespace(self::NAMESPACE_PREFIX, self::NAMESPACE_ENVIRONAUT_1_0);
    }

    /**
     * Returns whether the given document instance is an Environaut configuration file.
     *
     * @param DOMDocument $doc document to check
     *
     * @return bool true if it's an Environaut document; false otherwise
     */
    public static function isEnvironautConfigurationDocument(DOMDocument $doc)
    {
        return (
            $doc->documentElement &&
            $doc->documentElement->localName === 'environaut' &&
            $doc->documentElement->namespaceURI === self::NAMESPACE_ENVIRONAUT_1_0
        );
    }

    /**
     * Re-instantiates the internal xpath instance and then
     * registers the Environaut config namespace if necessary.
     */
    protected function refreshXpath()
    {
        unset($this->xpath);

        $this->xpath = new \DOMXPath($this);

        if ($this->isEnvironautDocument()) {
            $this->setDefaultNamespace(self::NAMESPACE_PREFIX, self::NAMESPACE_ENVIRONAUT_1_0);
        }
    }

    /**
     * Disables libXml errors to let us handle errors by ourselves.
     *
     * @return bool whether or not the internal error handling was enabled before
     */
    protected function enableErrorHandling()
    {
        $user_error_handling = libxml_use_internal_errors(true);
        libxml_clear_errors();
        return $user_error_handling;
    }

    /**
     * Evaluates the last libXml operation and throws an exception
     * with a verbose multiline error message as text.
     *
     * @param string $msg_prefix prefix for the error message of the exception
     * @param string $msg_suffix suffix for the error message of the exception
     * @param bool $user_error_handling
     *
     * @throws \DOMException when libXml errors occurred
     */
    protected function handleErrors($msg_prefix = '', $msg_suffix = '', $user_error_handling = false)
    {
        if (libxml_get_last_error() !== false) {
            $errors = libxml_get_errors();
            libxml_clear_errors();
            libxml_use_internal_errors($user_error_handling);

            throw new \DOMException(
                $msg_prefix .
                $this->getErrorMessage($errors) .
                $msg_suffix
            );
        }

        libxml_use_internal_errors($user_error_handling);
    }

    /**
     * Returns a formatted error message from the given libXml errors.
     *
     * @param array $errors array of \LibXMLError instances
     *
     * @return string formatted error message
     */
    protected function getErrorMessage(array $errors)
    {
        $error_message = '';

        foreach ($errors as $error) {
            $error_message .= $this->parseError($error) . PHP_EOL . PHP_EOL;
        }

        return $error_message;
    }

    /**
     * Formats the given libXml error as a multiline string.
     *
     * @param \LibXMLError $error error to get as formatted string
     *
     * @return string formatted error message
     */
    protected function parseError(\LibXMLError $error)
    {
        $msg = '';
        switch ($error->level) {
            case LIBXML_ERR_WARNING:
                $msg .= 'Warning ' . $error->code . ': ';
                break;
            case LIBXML_ERR_FATAL:
                $msg .= 'Fatal error: ' . $error->code . ': ';
                break;
            case LIBXML_ERR_ERROR:
            default:
                $msg .= 'Error ' . $error->code . ': ';
                break;
        }

        $msg .= trim($error->message) . PHP_EOL .
                '  Line: ' . $error->line . PHP_EOL .
                'Column: ' . $error->column;

        if ($error->file) {
            $msg .= PHP_EOL . '  File: ' . $error->file;
        }

        return $msg;
    }
}
