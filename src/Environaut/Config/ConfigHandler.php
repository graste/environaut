<?php

namespace Environaut\Config;

use Environaut\Checks\Validator;
use Environaut\Config\BaseConfigHandler;
use Environaut\Config\Reader\IConfigReader;
use Environaut\Config\Reader\JsonConfigReader;
use Environaut\Config\Reader\PhpConfigReader;
use Environaut\Config\Reader\XmlConfigReader;

/**
 * Default config handler that reads a config file from the
 * given locations. Supported are JSON, XML and PHP config
 * file formats.
 */
class ConfigHandler extends BaseConfigHandler
{
    /**
     * @var array supported config file extensions
     */
    protected $supported_file_extensions = array('json', 'xml', 'php');

    /**
     * @var array default config file names (order is important)
     */
    protected $default_filenames = array('environaut.xml', 'environaut.json', 'environaut.php');

    /**
     * Reads a config file from the given location and returns it's
     * data as an associative array.
     *
     * @param mixed $location location of config file (as a string with a file or directory path)
     *
     * @return array config data
     *
     * @throws \InvalidArgumentException on errors like missing config file
     */
    protected function readLocation($location)
    {
        $reader = null;

        if (is_dir($location)) {
            $base_location = Validator::fixPath(Validator::fixRelativePath($location));
            foreach ($this->default_filenames as $filename) {
                $file = $base_location . $filename;
                if (is_readable($file)) {
                    try {
                        $reader = $this->getReaderByExtension($file);
                        break; // found possible config file \o/
                    } catch (\InvalidArgumentException $e) {
                        // next attempt as extension has no handler
                    }
                }
                // next attempt, as file is not readable or does not exist
            }

            if (!$reader instanceof IConfigReader) {
                throw new \InvalidArgumentException(
                    'Could not find an environaut config file in "' . $base_location . '".' . PHP_EOL .
                    'Attempted files were: ' . implode(', ', $this->default_filenames) . '.' . PHP_EOL .
                    'Try calling from a different folder or specify a file using the "--config" option.'
                );
            }
        } elseif (is_file($location)) {
            $reader = $this->getReaderByExtension($location);
        } else {
            throw new \InvalidArgumentException(
                'Currently only regular files and directories are supported for config file reading.'
            );
        }

        $config_data = $reader->getConfigData($location);

        return $config_data;
    }

    /**
     * Returns a specific reader instance depending on the file
     * extension of the given config file location.
     *
     * @param string $location
     *
     * @return IConfigReader
     *
     * @throws \InvalidArgumentException in case of unsupported file extensions
     */
    protected function getReaderByExtension($location)
    {
        $ext = pathinfo($location, PATHINFO_EXTENSION);

        $reader = null;

        switch ($ext) {
            case 'json':
                $reader = new JsonConfigReader();
                break;
            case 'xml':
                $reader = new XmlConfigReader();
                break;
            case 'php':
                $reader = new PhpConfigReader();
                break;
            default:
                throw new \InvalidArgumentException(
                    'File could not be read: ' . $location . PHP_EOL .
                    'Supported config file extensions are: ' . implode(', ', $this->supported_file_extensions)
                );
                break;
        }

        return $reader;
    }
}
