<?php

namespace Environaut\Checks;

/**
 * Default validator class that may be used in checks
 * for common types of data (esp. the Configurator).
 */
class Validator
{
    /**
     * Validates the given path and throws an exception
     * if it is not a regular directory or readable.
     *
     * @param string $value value to check
     *
     * @return string path to valid directory
     *
     * @throws \InvalidArgumentException in case of validation errors (empty value, irregular or non-readable directory)
     */
    public static function readableDirectory($value)
    {
        if (empty($value)) {
            throw new \InvalidArgumentException("Empty path given.");
        }

        if (!is_dir($value)) {
            throw new \InvalidArgumentException("Given path is not a regular directory.");
        }

        if (!is_readable($value)) {
            throw new \InvalidArgumentException("Given directory is not readable.");
        }

        return $value;
    }

    /**
     * Validates the given path and throws an exception
     * if it is not a regular directory or writable.
     *
     * @param string $value value to check
     *
     * @return string path to valid directory
     *
     * @throws \InvalidArgumentException in case of validation errors (empty value, irregular or non-writable directory)
     */
    public static function writableDirectory($value)
    {
        if (empty($value)) {
            throw new \InvalidArgumentException("Empty path given.");
        }

        if (!is_dir($value)) {
            throw new \InvalidArgumentException("Given path is not a regular directory.");
        }

        if (!is_writable($value)) {
            throw new \InvalidArgumentException("Given directory is not writable.");
        }

        return $value;
    }

    /**
     * Validates the given file path and throws an exception
     * if it is not a regular file or writable.
     *
     * @param string $value value to check
     *
     * @return string path to valid file
     *
     * @throws \InvalidArgumentException in case of validation errors (empty value or irregular or non-writable file)
     */
    public static function writableFile($value)
    {
        if (empty($value)) {
            throw new \InvalidArgumentException("Empty file path given.");
        }

        if (!is_file($value)) {
            throw new \InvalidArgumentException("Given file path is not a regular file.");
        }

        if (!is_writable($value)) {
            throw new \InvalidArgumentException("Given file is not writable.");
        }

        return $value;
    }

    /**
     * Validates the given file path and throws an exception
     * if it is not a regular file or readable.
     *
     * @param string $value value to check
     *
     * @return string path to valid file
     *
     * @throws \InvalidArgumentException in case of validation errors (empty value or irregular or non-readable file)
     */
    public static function readableFile($value)
    {
        if (empty($value)) {
            throw new \InvalidArgumentException("Empty file path given.");
        }

        if (!is_file($value)) {
            throw new \InvalidArgumentException("Given file path is not a regular file.");
        }

        if (!is_readable($value)) {
            throw new \InvalidArgumentException("Given file is not readable.");
        }

        return $value;
    }

    /**
     * Validates the given URL and throws an exception if
     * it is not valid (Only http and https are valid as
     * protocols).
     *
     * @param string $value value to check
     *
     * @return string URL
     *
     * @throws \InvalidArgumentException in case of validation errors (invalid or empty URL)
     */
    public static function validUrl($value)
    {
        if (empty($value)) {
            throw new \InvalidArgumentException(
                "Empty URL given. Valid URLs have the format: http(s)://[(sub.)domain|ip][:port][/[optional_path]]"
            );
        }

        // this pattern has been taken from the symfony url validator:
        // https://github.com/symfony/Validator/blob/master/Constraints/UrlValidator.php
        // @author Bernhard Schussek <bschussek@gmail.com>
        $pattern = <<<EOT
~^
(http|https)://                         # protocols
(
    ([\pL\pN\pS-]+\.)+[\pL]+            # domain name
        |                               #  or
    \d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}  # IPv4 address
        |                               #  or
    \[
        (?:(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){6})(?:(?:(?:(?:(?:[0-9a-f]{1,4})):
        (?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}
        (?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:::(?:(?:(?:[0-9a-f]{1,4})):){5})
        (?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|
        (?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|
        (?:(?:(?:(?:(?:[0-9a-f]{1,4})))?::(?:(?:(?:[0-9a-f]{1,4})):){4})(?:(?:(?:(?:(?:[0-9a-f]{1,4})):
        (?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}
        (?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,1}
        (?:(?:[0-9a-f]{1,4})))?::(?:(?:(?:[0-9a-f]{1,4})):){3})(?:(?:(?:(?:(?:[0-9a-f]{1,4})):
        (?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}
        (?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,2}
        (?:(?:[0-9a-f]{1,4})))?::(?:(?:(?:[0-9a-f]{1,4})):){2})(?:(?:(?:(?:(?:[0-9a-f]{1,4})):
        (?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}
        (?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,3}
        (?:(?:[0-9a-f]{1,4})))?::(?:(?:[0-9a-f]{1,4})):)(?:(?:(?:(?:(?:[0-9a-f]{1,4})):
        (?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}
        (?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,4}
        (?:(?:[0-9a-f]{1,4})))?::)(?:(?:(?:(?:(?:[0-9a-f]{1,4})):(?:(?:[0-9a-f]{1,4})))|
        (?:(?:(?:(?:(?:25[0-5]|(?:[1-9]|1[0-9]|2[0-4])?[0-9]))\.){3}(?:(?:25[0-5]|
        (?:[1-9]|1[0-9]|2[0-4])?[0-9])))))))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,5}
        (?:(?:[0-9a-f]{1,4})))?::)(?:(?:[0-9a-f]{1,4})))|(?:(?:(?:(?:(?:(?:[0-9a-f]{1,4})):){0,6}
        (?:(?:[0-9a-f]{1,4})))?::))))
    \]                                  # IPv6 address
)
(:[0-9]+)?                              # port (optional)
(/?|/\S+)                               # /, nothing or / with something
$~ixu
EOT;

        if (!preg_match($pattern, $value)) {
            throw new \InvalidArgumentException(
                "Invalid URL given. Valid URLs have the format: http(s)://[(sub.)domain|ip][:port][/[optional_path]]"
            );
        }

        return $value;
    }

    /**
     * Validates the given value using the PHP filter_var
     * with FILTER_VALIDATE_EMAIL option.
     *
     * @param string $value value to check
     *
     * @return string email
     *
     * @throws \InvalidArgumentException in case of validation errors (invalid or empty email)
     */
    public static function validEmail($value)
    {
        if (is_object($value) || empty($value)) {
            throw new \InvalidArgumentException("Empty email given.");
        }

        if (!filter_var((string)$value, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException("Invalid email address given.");
        }

        return $value;
    }

    /**
     * Validates the given value using the PHP filter_var with
     * FILTER_VALIDATE_IP.
     *
     * @param string $value IP to validate
     *
     * @return string ipv4 or ipv6 (may still be in private or reserved range)
     *
     * @throws \InvalidArgumentException in case of validation errors (invalid or empty ip)
     */
    public static function validIp($value)
    {
        if (empty($value)) {
            throw new \InvalidArgumentException("Empty IP address given.");
        }

        if (!filter_var($value, FILTER_VALIDATE_IP)) {
            throw new \InvalidArgumentException("Invalid IP address given.");
        }

        return $value;
    }

    /**
     * Validates the given value using the PHP filter_var with
     * FILTER_VALIDATE_IP and flag FILTER_FLAG_IPV4.
     *
     * @param string $value IP to validate
     *
     * @return string ipv4 (may still be in private or reserved range)
     *
     * @throws \InvalidArgumentException in case of validation errors (invalid or empty ip)
     */
    public static function validIpv4($value)
    {
        if (empty($value)) {
            throw new \InvalidArgumentException("Empty IPv4 address given.");
        }

        if (!filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            throw new \InvalidArgumentException("Invalid IPv4 address given.");
        }

        return $value;
    }

    /**
     * Validates the given value using the PHP filter_var with
     * FILTER_VALIDATE_IP and flag FILTER_FLAG_IPV4 | FILTER_FLAG_NO_RES_RANGE.
     *
     * @param string $value IP to validate
     *
     * @return string ipv4 (may still be in private range)
     *
     * @throws \InvalidArgumentException in case of validation errors (invalid or empty ip)
     */
    public static function validIpv4NotReserved($value)
    {
        if (empty($value)) {
            throw new \InvalidArgumentException("Empty IPv4 address given.");
        }

        if (!filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_RES_RANGE)) {
            throw new \InvalidArgumentException(
                "Invalid IPv4 address given. Reserved ranges like 240.0.0.0/4 are disallowed."
            );
        }

        return $value;
    }

    /**
     * Validates the given value using the PHP filter_var with
     * FILTER_VALIDATE_IP and flag FILTER_FLAG_IPV6
     *
     * @param string $value IP to validate
     *
     * @return string ipv6 (may still be in private or reserved range)
     *
     * @throws \InvalidArgumentException in case of validation errors (invalid or empty ip)
     */
    public static function validIpv6($value)
    {
        if (empty($value)) {
            throw new \InvalidArgumentException("Empty IPv6 address given.");
        }

        if (!filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            throw new \InvalidArgumentException("Invalid IPv6 address given.");
        }

        return $value;
    }

    /**
     * Resolves dots and double slashes in relative paths to get
     * nicer paths: "dev/data/assets/../../foo" will be "dev/foo/"
     * and "foo/./bar" will be "foo/bar" etc.
     *
     * @return string
     */
    public static function fixRelativePath($path_with_dots)
    {
        do {
            $path_with_dots = preg_replace('#[^/\.]+/\.\./#', '', $path_with_dots, -1, $count);
        } while ($count);

        $path_with_dots = str_replace(array('/./', '//'), '/', $path_with_dots);

        return $path_with_dots;
    }

    /**
     * Appends '/' to the path if necessary.
     *
     * @param string $path file system path
     *
     * @return string path with trailing slash
     */
    public static function fixPath($path)
    {
        if (empty($path)) {
            return $path;
        }

        if ('/' != $path{strlen($path) - 1}) {
            $path .= '/';
        }

        return $path;
    }
}
