<?php

namespace Environaut\Config\Reader;

class PhpConfigReader implements IConfigReader
{
    public function getConfigData($location)
    {
        return $this->getExampleConfiguration($location);

        if (is_dir($location)) {
            $location = $location . DIRECTORY_SEPARATOR . 'environaut.php';
        }

        if (!is_readable($location)) {
            throw new \InvalidArgumentException("Configuration file not readable: $location");
        }

        $config = include($location);

        return $config;
    }

    public static function getExampleConfiguration()
    {
        $base_href_params = array(
            'name' => 'base_href',
            'class' => 'Environaut\Checks\Configurator',
            'setting_name' => 'base_href',
            'question' => 'Wie lautet der BaseHref?',
            'default' => 'http://honeybee-showcase.dev/',
            'choices' => array('http://cms.honeybee-showcase.dev/', 'http://google.de/', 'http://heise.de/'),
            'validator' => 'Environaut\Checks\Validator::validUrl',
            //'validator' => 'Foo\Validator::validUrl',
            'max_attempts' => 5
        );

        $simple_string_params = array(
            'name' => 'trololo',
            'class' => 'Environaut\Checks\Configurator',
            'setting_name' => 'contact.name',
            'introduction' => "Trololo is a video of the nationally-honored Russian singer Eduard Khil (AKA Edward Khill, Edward Hill) performing the Soviet-era pop song “I am Glad, ‘cause I’m Finally Returning Back Home” (Russian: Я очень рад, ведь я, наконец, возвращаюсь домой). The video is often used as a bait-and-switch prank, in similar vein to the practice of Rickrolling.\n\nSource: http://knowyourmeme.com/memes/trololo-russian-rickroll\n\n",
            'question' => 'Wie lautet der Vorname des Trololo Manns?',
            'choices' => array('Mr.', 'Eduard', 'Edward', 'omgomgomg'),
        );

        $simple_email_params = array(
            'name' => 'contact',
            'class' => 'Environaut\Checks\Configurator',
            'setting_name' => 'contact.email',
            'question' => 'Wie lautet seine Emailadresse?',
            'choices' => array('mr.trololo@example.com'),
            'validator' => 'Environaut\Checks\Validator::validEmail',
            'max_attempts' => 5
        );

        $confirmation_params = array(
            'name' => 'confirm',
            'class' => 'Environaut\Checks\Configurator',
            'setting_name' => 'testing',
            'question' => 'Testmodus aktivieren?',
            'default' => false,
            'confirm' => true
        );

        $password_params = array(
            'name' => 'password',
            'class' => 'Environaut\Checks\Configurator',
            'setting_name' => 'super_secret_password',
            'question' => 'Wie lautet das geheime Passwort?',
            'hidden' => true,
            'allow_fallback' => true,
        );

        $select_params = array(
            'name' => 'selection',
            'class' => 'Environaut\Checks\Configurator',
            'setting_name' => 'selected_url',
            'question' => 'Welche URL bevorzugen Sie?',
            //'default' => 1,
            'choices' => array('http://cms.honeybee-showcase.dev/', 'http://google.de/', 'http://heise.de/'),
            'select' => true,
        );

        $ip_params = array(
            'name' => 'valid_ip',
            'class' => 'Environaut\Checks\Configurator',
            'setting_name' => 'core.ipv4',
            'question' => 'Geben Sie bitte eine valide nicht-reservierte, nicht-private IPv4-Adresse ein:',
            'default' => '195.74.70.239',
            'choices' => array('240.0.0.1', '192.168.1.100', '127.0.0.1', '172.16.1.100', '10.0.0.1'),
            'validator' => 'Environaut\Checks\Validator::validIpv4NotReserved',
        );

        $cache_dir_params = array(
            'name' => 'cache_dir',
            'class' => 'Environaut\Checks\Configurator',
            'setting_name' => 'core.cache_dir',
            'question' => 'Geben Sie bitte ein schreibbares Verzeichnis an:',
            'choices' => array('cache', '/tmp', './tests'),
            'validator' => 'Environaut\Checks\Validator::writableDirectory',
        );

//        $custom = array(
//            'name' => 'password',
//            'class' => 'Foo\PhpSetting',
//        );

        $config = array(
            'name' => 'graste/environaut/example',
            'description' => 'Some check configurations as an example for a small (interactive) environment check.',
            'keywords' => array("environment", "requirements", "configuration", "check", "cli", "php"),
            'implementor1' => 'asdf',
            'implementor2' => 'sfdg',
            'checks' => array(
                $cache_dir_params,
                $ip_params,
                $select_params,
                $base_href_params,
                $confirmation_params,
                $simple_string_params,
                $simple_email_params,
                $password_params,
            )
        );

        return $config;
    }

}
