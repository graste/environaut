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
- attributes are merged over the parameters

You can just use JSON or even a PHP file (with arrays) as well if you don't
like XML. Please note, that no schema validation is done for JSON or PHP.

## Configuration File Format

*The configuration has really only one mandatory element:
A list of checks to perform.*

Have a look at [examples/minimal.example.json](examples/minimal.example.json)
for a small config that asks a user for two settings. The [complex.example](examples/complex.example.json)
has some more options. The [environaut.xml](../environaut.xml) is a rather
extensive example that XIncludes checks from a pool of checks.

Environaut runs all checks defined in the configuration files with their respective parameters.

The following parts of the config file are available for Environaut
configuration:

- `name` [optional]: just a name you give your application's Environaut configuration
- `description` [optional]: a description for users and developers of the Environaut configuration
- `keywords` [optional]: just a bunch of tags that describes this configuration
- `introduction` [optional]: a description that is displayed to the user that uses Environaut
- `cache` [optional]: configuration about the caching of settings that are emitted from checks
- `runner` [optional]: configuration about how to run the checks
- `report` [optional]: configuration about how to collect, handle and compile results of checks into a report
- `export` [optional]: configuration about how to export a report (containing the check results)
- `checks` [optional]: checks with their respective configuration

### Checks

A check is a class that implements `Environaut\Checks\ICheck`. A check can just
do something useful and return a result. The result may contain messages and
(cachable) settings that the check emits.

- `class` [required] (or `__class` in php/json config): namespaced class name of the check.
    - format is ```Custom\Class\Name``` (or simple class names without namespace if you like)
    - must implement `ICheck` interface
    - that should be autoloadable from current folder (or see the ```autoload-dir``` CLI option)
    - `run` method must return a boolean result whether the check was successful or not
    - check may emit messages
    - check may emit settings
    - emitted settings may be cachable for later runs (accessible via property `cache`)
- `name` [optional] (or `__name` in php/json config): name of the check
    - best if unique value
- `group` [optional] (or `__group` in php/json config): name of a group this check and it's results belong to
    - defaults to `default` or whatever the check implementation defines
    - is useful to separate emitted settings in different groups for the export and report display
- parameters: list of `name => value` pairs
    - in XML configs: ```<parameter name="[name]">[value]</parameter>```
    - in JSON configs: ```"[name]": "[value]"```
    - in PHP configs: ```"[name]" => "[value]"```
    - see config file examples in `docs/examples` or `environaut.xml` in the root folder

### Cache

The cache is used to store cachable settings that are emitted from checks. The cached settings
may then be reused in those checks on later subsequent runs to e.g. let users just confirm the
values instead of having to retype everything.

The following parameters are used by Environaut:

- `location` [optional]: file path and name of cache file to use for reading/writing of cachable settings
- `read_location` [optional]: path to cache file to read cached settings from; overrides `location`
- `write_location` [optional]: path to cache file to write cached settings to; overrides `location`
- `class` [optional] (or `__class` in php/json config): namespaced class name implementing `ICache` (to override default behaviours if needed)
- `readonly_class` [optional]: namespaced class name implementing `IReadOnlyCache` (to override default behaviours if needed)

The reading from and writing to cache files can be disabled completely by using the `check`
commandline option `--no-cache`.

The location of the cache file specified in the Environaut configuration file may
be overridden by the `check` commandline option `--cache-location [path]`. If the
CLI specified file does not exist or is not readable/writable Environaut falls back
to the configured values from the configuration or the default `.environaut.cache`
in the current working directory.

### Export

May be used to override the export class used internally to present the compiled report
to the user. This usually includes displaying the emitted messages from the checks on the
CLI and writing of the emitted settings to one or more files to be included in your
application.

- `class` [optional] (or `__class` in php/json config): namespaced class name implementing `IExport` (to override default behaviours if needed)

The default export class uses `formatters`. A `formatter` defines the format and location
of the file to export to and returns an output string to display on the CLI.

- `location` [optional]: file path and name to use
- `format` [optional]: `xml`, `json` or `php`

You do not need to specify the format if the `location` ends with a wel known extension
as the correct formatter class will be chosen depending on the file extension.

When you want to use a custom formatter you need to specify a classname:

- `class` [optional] (or `__class` in php/json config): namespaced class name implementing `IReportFormatter` (for custom formatters)

That class is instantiated and gets the specified child parameters of the formatter
to be able to configure the runtime behaviour.

### Report

May be used to override the report class used internally to compile results of run checks.
The specified parameters are givent to the custom class as well if specified.

- `class` [optional] (or `__class` in php/json config): namespaced class name implementing `IReport`

### Runner

May be used to override the runner class used internally to run checks and collect a report.

- `class` [optional] (or `__class` in php/json config): namespaced class name implementing `IRunner`
