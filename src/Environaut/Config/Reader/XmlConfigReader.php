<?php

namespace Environaut\Config\Reader;

use Environaut\Config\Reader\IConfigReader;

/**
 * Reads configuration data from XML formatters.
 */
class XmlConfigReader implements IConfigReader
{
    /**
     * @var Environaut\Config\Reader\Dom\DomDocument
     */
    protected $doc;

    /**
     * Reads the config from the given location and
     * returns the data as an associative array.
     *
     * @param mixed $location location to read config data from (usually file/directory path)
     *
     * @return array config data as associative array
     *
     * @throws \InvalidArgumentException in case of problems handling the given location
     * @throws \DomException in case of schema validation errors
     */
    public function getConfigData($location)
    {
        if (is_dir($location)) {
            $location = $location . DIRECTORY_SEPARATOR . 'environaut.xml';
        }

        $this->doc = $doc = new Dom\DomDocument();
        $doc->load($location, LIBXML_NOCDATA);
        $doc->xinclude(LIBXML_NOCDATA);
        $doc->schemaValidate(__DIR__ . '/Schema/environaut.xsd');

        $config_data = array();

        $value = $doc->getElementValue('name');
        if (null !== $value) {
            $config_data['name'] = $value;
        }

        $value = $doc->getElementValue('description');
        if (null !== $value) {
            $config_data['description'] = $value;
        }

        $value = $doc->getElementValue('introduction');
        if (null !== $value) {
            $config_data['introduction'] = $value;
        }

        $keywords_element = $doc->getElement('keywords');
        if (null !== $keywords_element) {
            $keywords = array();
            foreach ($keywords_element->getChildren('keyword') as $keyword) {
                $keywords[] = (string) $keyword;
            }
            if (!empty($keywords)) {
                $config_data['keywords'] = $keywords;
            }
        }

        $element = $doc->getElement('cache');
        if (null !== $element) {
            $config_data['cache'] = $element->getParameters();
            $value = $element->getAttributeValue('class');
            if (null !== $value) {
                $config_data['cache']['__class'] = $value;
            }
        }

        $element = $doc->getElement('report');
        if (null !== $element) {
            $config_data['report'] = $element->getParameters();
            $value = $element->getAttributeValue('class');
            if (null !== $value) {
                $config_data['report']['__class'] = $value;
            }
        }

        $element = $doc->getElement('runner');
        if (null !== $element) {
            $config_data['runner'] = $element->getParameters();
            $value = $element->getAttributeValue('class');
            if (null !== $value) {
                $config_data['runner']['__class'] = $value;
            }
        }

        $element = $doc->getElement('export');
        if (null !== $element) {
            $config_data['export'] = $element->getParameters();
            $formatter_nodes = $element->get('formatters');
            $formatters = array();
            foreach ($formatter_nodes as $formatter_node) {
                $attributes = $formatter_node->getAttributes();
                foreach ($attributes as $name => $value) {
                    if ($name === 'class') {
                        $attributes['__class'] = $value;
                    }
                }
                unset($attributes['class']);
                $parameters = $formatter_node->getParameters();
                $formatters[] = array_merge($parameters, $attributes);
            }
            $config_data['export']['formatters'] = $formatters;
            $value = $element->getAttributeValue('class');
            if (null !== $value) {
                $config_data['export']['__class'] = $value;
            }
        }

        $element = $doc->getElement('checks');
        if (null !== $element) {
            $checks = array();
            $check_nodes = $element->get('check');
            foreach ($check_nodes as $check) {
                $attributes = $check->getAttributes();
                foreach ($attributes as $name => $value) {
                    if ($name === 'class') {
                        $attributes['__class'] = $value;
                    } elseif ($name === 'name') {
                        $attributes['__name'] = $value;
                    } elseif ($name === 'group') {
                        $attributes['__group'] = $value;
                    }
                }
                unset($attributes['class']);
                unset($attributes['name']);
                unset($attributes['group']);
                $parameters = $check->getParameters();
                $checks[] = array_merge($parameters, $attributes);
            }
            $config_data['checks'] = $checks;
        }

        return $config_data;
    }

    /**
     * Returns the DomDocument instance that was used for reading the config file.
     *
     * @return Environaut\Config\Reader\Dom\DomDocument or null
     */
    public function getDocument()
    {
        return $this->doc;
    }
}
