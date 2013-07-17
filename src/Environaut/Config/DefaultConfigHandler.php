<?php

namespace Environaut\Config;

use Environaut\Config\Reader\JsonConfigReader;
use Environaut\Config\Reader\PhpConfigReader;
use Environaut\Config\Reader\XmlConfigReader;

class DefaultConfigHandler extends BaseConfigHandler
{
    protected function readLocation($location)
    {
        $reader = null;

        $reader = new PhpConfigReader();
        $config_data = $reader->getConfigData($location);
        return $config_data;

        if (is_dir($location)) {
            $base_location = $this->fixPath($this->fixRelativePath($location));
            $attempts = array('environaut.json', 'environaut.xml', 'environaut.php');
            foreach ($attempts as $filename) {
                $file = $base_location . $filename;
                if (is_readable($file)) {
                    try {
                        $reader = $this->getReader($file);
                        break; // found possible config file \o/
                    } catch (\InvalidArgumentException $e) {
                        // next attempt as extension has no handler
                    }
                }
                // next attempt, as file is not readable or does not exist
            }

            if (!$reader instanceof IConfigReader) {
                throw new \InvalidArgumentException('Could not find a environaut config file in "' . $base_location . '". Attempted files were: ' . implode(', ', $attempts));
            }
        } else if (is_file($location)) {
            $reader = $this->getReader($location);
            $config_data = $reader->read($location);
        } else {
            throw new \InvalidArgumentException('Currently only regular files and directories are supported for config file reading.');
        }

        $config_data = $reader->getConfigData($location);

        return $config_data;
    }

    protected function getReader($location)
    {
        $ext = pathinfo($location, PATHINFO_EXTENSION);

        $reader = null;

        switch ($ext) {
            case 'php':
                $reader = new PhpConfigReader();
                break;
            case 'xml':
                $reader = new XmlConfigReader();
                break;
            case 'json':
                $reader = new JsonConfigReader();
                break;
            default:
                throw new \InvalidArgumentException('Supported config file extensions are ".xml", ".json" and ".php". File could not be read: ' . $location);
                break;
        }

        return $reader;
    }
}
