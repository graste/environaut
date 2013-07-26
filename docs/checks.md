# Configuration

## Supported formats

The configuration formats `Environaut` supports are:

- XML
- JSON
- PHP

By default `Environaut` searches for configuration files in the current
working directory:

1. environaut.xml
2. environaut.json
3. environaut.php

The first found file wins and the configuration is read from that file.
You can specify what config file to use be giving a valid config file
(directory) via the ```--config | -c``` commandline option of the `check`
command.

The usage of the XML format is preferred:

- the XML config file is schema validated (albeit loosely specified)
- you can XInclude checks from other config files or your pool of checks
  (this could be used to XInclude checks for all your application's bundles)
- you can preserve whitespace on parameters via ```space="preserve"``` attribute
- you can disable literalization of values via the ```literalize="false"```
  attribute. Usually the following parameter values are literalized:
    - string `"true|false"` is read as boolean `true|false`
    - string `"on|off"` is read as boolean `true|false`
    - string `"yes|no"` is read as boolean `true|false`

You can just use JSON or even a PHP file (with arrays) as well if you don't
like XML. Please note, that no schema validation is done for JSON or PHP.

## Configurator

Simple check that asks questions and emits settings in the result that may be
used to export a configuration file. It supports multiple types of questions:

- input for a value of a setting
- input for confirmation of a setting
- input hidden from screen (for credentials)
- select values from a list of choices

The input can be constrained in several ways:

- may be hidden from screen output (with fallback to visible input)
- validate values given by user
- maxmimum number of attempts a user is allowed to input values
- default values may be specified (so user can just confirm the with `return`)

### Value Confirmation

A simple confirmation of a yes/no question with a default of `yes` or `no`.
The user has to type `y` or `n`. Simply confirming via `return` uses the
specified `default` value.

```json
{
    "__name": "simple_confirm"
    "__class": "Environaut\\Checks\\Configurator",
    "__group": "default",
    "setting_name": "core.testing_enabled",
    "question": "Enable test mode?",
    "default": true,
    "confirm": true
}
```

or as XML:

```xml
<check name="core.testing_enabled">
    <parameter name="question">Enable test mode?</parameter>
    <parameter name="default">true</parameter>
    <parameter name="confirm">true</parameter>
</check>
```

As you can see the `Configurator` check is the default class that is used
by Environaut when no class for a check is specified. These are the default
values of the `Environaut\Checks\Configurator`:

- `__group` (or `group` attribute for XML) defaults to `default`
- `setting` defaults to the `__name` (or `name` attribute for XML)

### Value Input with Autocomplete and Validation

A value is being asked for. The ```setting``` parameter specifies the key
to use for the setting. The value is input by the person configuring the
environment. A `default` value may be used by just confirming it via `return`.

```json
{
    "__name": "base_href",
    "__class": "Environaut\\Checks\\Configurator",
    "setting": "core.base_href",
    "question": "Base HREF to use",
    "default": "http:\/\/honeybee-showcase.dev\/",
    "choices": [
        "http:\/\/cms.honeybee-showcase.dev\/",
        "http:\/\/google.de\/",
        "http:\/\/heise.de\/"
    ],
    "validator": "Environaut\\Checks\\Validator::validUrl",
    "max_attempts": 5
}
```

or as XML:

```xml
<check name="core.base_href">
    <parameter name="question">Base HREF to use</parameter>
    <parameter name="default">http://honeybee-showcase.dev/</parameter>
    <parameter name="choices">
        <parameter>http://cms.honeybee-showcase.dev/</parameter>
        <parameter>http://google.de/</parameter>
        <parameter>http://heise.de/</parameter>
    </parameter>
    <parameter name="validator">Environaut\Checks\Validator::validUrl</parameter>
    <parameter name="max_attempts">5</parameter>
</check>
```


The values in the `choices` array are available as autocomple values. Using the
cursor keys they can be picked quickly. It's of course still possible to enter a
completely different URL. The user input is validated using the static
`validUrl` method of the `Validator` class. At a maximum the user is allowed to
need five attempts to input a valid URL. If he fails, an exception is thrown
and the environment check and configuration comes to an abrupt stop. By default
the number of attempts specified via ```max_attempts``` is _unlimited_.

### Value Selection

A value may be selected from a predefined list. To enable a selection display
set `select` to `true`:

```xml
<check name="core.selected_url">
    <parameter name="question">What is the URL you want to use?</parameter>
    <parameter name="select">true</parameter>
    <parameter name="choices">
        <parameter>http://cms.honeybee-showcase.dev/</parameter>
        <parameter>http://google.de/</parameter>
        <parameter>http://heise.de/</parameter>
    </parameter>
</check>
```

### Hidden Input

It is possible to ask for hidden input. That is, the input is not echoed on the
screen when a user types something. This is useful for sensitive information
like account credentials, passwords etc.. Just set `hidden` to `true` to enable
the hidden input:

```xml
<check name="password">
    <parameter name="question">Super secret password</parameter>
    <parameter name="hidden">true</parameter>
</check>
```

By setting ```allow_fallback``` to `false` you can disable the fallback to a
normal input if the current environment doesn't allow hidden value input.

## Available Default Validations

`Environaut` has some builtin default validation methods that may be useful with
the Configurator check. Usage is always the same: Specify a `Callable` in the
`validator` parameter and make sure, that the given method returns the validated
value if everything's okay or throws an exception if the value is invalid. An
example for the question for a writable cache dir is this:

```xml
<check name="cache_dir">
    <parameter name="question">Please input the cache dir to use</parameter>
    <parameter name="select">true</parameter>
    <parameter name="choices">
        <parameter>cache</parameter>
        <parameter>/tmp</parameter>
        <parameter>/app/cache</parameter>
    </parameter>
    <parameter name="validator">Environaut\\Checks\\Validator::writableDirectory</parameter>
</check>
```

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

```xml
<check name="composer-security" class="Environaut\Checks\ComposerSecurityCheck" />
```

This check needs `cURL` (`libcurl`) to work. Additional parameters are:

- `file`: path to the Composer configuration file to use (defaults to current working directory)
- `silent`: flag whether to output a text on CLI when checking (defaults to `true`)


