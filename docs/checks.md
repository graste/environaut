# Configuration

## Supported formats

The configuration formats `Environaut` supports are:

- JSON
- PHP
- XML (not yet)

By default `Environaut` searches for configuration files in the current
directory:

1. environaut.json
2. environaut.xml
3. environaut.php

The first found file wins and the configuration is read from that file.

## Configurator

Simple check that asks questions and emits settings in the result that may be
used to export a configuration file. It supports multiple types of questions:

- input for a value of a setting
- input for confirmation of a setting
- input hidden from screen (for credentials)
- select values from a list of choices

The input can be constrained:

- may be hidden from screen output
- validated after being entered
- maxmimum number of attempts to input values can be set
- default values may be specified

### Value Confirmation

A simple confirmation of a yes/no question with a default of yes or no. The
user has to type `y` or `n`. Simply confirming via `return` uses the specified
`default` value.

    {
        "name": "simple_confirm"
        "class": "Environaut\\Checks\\Configurator",
        "setting_name": "core.testing_enabled",
        "question": "Enable test mode?",
        "default": true,
        "confirm": true
    }

### Value Input with Autocomplete and Validation

A value is being asked for. The ```setting_name``` parameter specifies the key
to use for the setting. The value is input by the person configuring the
environment. A `default` value may be used by just confirming it via `return`.

    {
        "name": "base_href",
        "class": "Environaut\\Checks\\Configurator",
        "setting_name": "core.base_href",
        "question": "Base href to use",
        "default": "http:\/\/honeybee-showcase.dev\/",
        "choices": [
            "http:\/\/cms.honeybee-showcase.dev\/",
            "http:\/\/google.de\/",
            "http:\/\/heise.de\/"
        ],
        "validator": "Environaut\\Checks\\Validator::validUrl",
        "max_attempts": 5
    }

The values in the `choices` array are available as autocomple values. Using the
cursor keys they can be picked fast. It is still possible to just enter a
completely different URL. The user input is validated using the static
`validUrl` method of the `Validator` class. At a maximum the user is allowed to
need five attempts to input a valid URL. If he fails, an exception is thrown
and the environment check and configuration comes to an abrupt stop. By default
the number of attempts specified via ```max_attempts``` is unlimited.

### Value Selection

A value may be selected from a predefined list. A `default` value is still
possible. To enable a selection display set `select` to `true`:

    {
        "name": "selection",
        "class": "Environaut\\Checks\\Configurator",
        "setting_name": "core.selected_url",
        "question": "What is the URL you want to use?",
        "choices": [
            "http:\/\/cms.honeybee-showcase.dev\/",
            "http:\/\/google.de\/",
            "http:\/\/heise.de\/"
        ],
        "select": true
    }

### Hidden Input

It is possible to ask for hidden input. That is, the input is not echoed on the
screen when a user types something. This is useful for sensitive information
like account credentials, passwords etc.. Just set `hidden` to `true` to enable
the hidden input:

    {
        "name": "password",
        "class": "Environaut\\Checks\\Configurator",
        "setting_name": "core.db.password",
        "question": "Database password",
        "hidden": true,
        "allow_fallback": false
    }

By setting ```allow_fallback``` to `false` you can disable the fallback to a
normal input if the current environment doesn't allow hidden value input.

## Available Default Validations

`Environaut` has some builtin default validation methods that may be useful with
the Configurator check. Usage is always the same: Specify a `Callable` in the
`validator` parameter and make sure, that the given method returns the validated
value if everything's okay or throws an exception if the value is invalid. An
example for the question for a writable cache dir is this:

        {
            "name": "cache_dir",
            "class": "Environaut\\Checks\\Configurator",
            "setting_name": "core.cache_dir",
            "question": "Please input the cache dir to use",
            "choices": [
                "cache",
                "\/tmp",
                ".\/app\/cache"
            ],
            "validator": "Environaut\\Checks\\Validator::writableDirectory"
        }

Available validation methods can be found in the ```Environaut\Checks\Validator```:

- `readableDirectory`: readable regular directory
- `writableDirectory`: writable regular directory
- `readableFile`: readable regular file
- `writableFile`: writable regular file
- `validUrl`: URL of format ```http(s)://[[sub.]domain|ip][:port][/[optional_path]]```
- `validEmail`: valid email according to PHP's ```filter_var``` method
- `validIp`: valid IPv4 or IPv6 address (without restrictions)
- `validIpv4`: valid IPv4 address
- `validIpv6`: valid IPv6 address
- `validIpv4NotReserved`: valid IPv4 address from a non-reserved range

## ComposerSecurityCheck

This check uses the (SensioLabs Security Checker)[https://github.com/sensiolabs/security-checker]
to check a `composer.lock` file for known security vulnerabilities in the
defined vendor libraries. Usage is as follows:

    {
        "name": "composer-security",
        "class": "Environaut\\Checks\\ComposerSecurityCheck"
    }

This check needs `cURL` (`libcurl`) to work. Additional parameters are:

- `file`: path to the Composer configuration file to use (defaults to current working directory)
- `silent`: flag whether to output a text on CLI when checking (defaults to `true`)


