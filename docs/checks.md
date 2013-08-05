# Checks

There are a few checks that are already present for direct usage:

- [`Configurator`](#configurator) - ask user for (cachable) values and emit them as grouped config settings for export formatters
- `PhpSettingCheck` - validate php.ini settings for correct values
- `PhpExtensionCheck` - validate presence and configuration of PHP extensions
- `ExecutableCheck` - ask or find absolute paths to emit those values as (cachable) settings for export formatters
- [`ComposerSecurityCheck`](#composersecuritycheck) - check a composer.lock file for well known security problems
- `MbInternalEncodingCheck` - check for mbstring extension being present and using UTF-8 as internal encoding

## Custom Checks

You can always create your own checks. All you need to do is to
create a class that implements the `ICheck` interface and do your
checking within the `run` method. Have a look at the existing checks
for inspiration and use the [`examples/YourCheckName.php`](examples/YourCheckName.php)
class as a template.

Checks must return `true` when they ran successfully, `false` when
they did not. Checks may fill their `Result` instance with `messages`
and `settings`. Messages can have different severities and settings
may be cachable or not and may be grouped.

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
    "__name": "simple_confirm",
    "__class": "Environaut\\Checks\\Configurator",
    "__group": "default",
    "setting_name": "core.testing_enabled",
    "question": "Enable test mode?",
    "default": true,
    "confirm": true
}
```

or as XML (in a shortened version):

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
    <parameter name="validator">Environaut\Checks\Validator::writableDirectory</parameter>
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


