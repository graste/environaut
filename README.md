# Environaut

* Latest Version: [![Latest Stable Version](https://poser.pugx.org/graste/environaut/version.png)](https://packagist.org/packages/graste/environaut)
* Build: [![Build Status](https://secure.travis-ci.org/graste/environaut.png)](http://travis-ci.org/graste/environaut)

Please have a look at the [available releases](https://github.com/graste/environaut/releases).

## Purpose

`Environaut` should enable and help developers to define the environment of an
application and check if all defined constraints are met. This includes
assertions and requirements of the application environment as well as some
configuration that may be necessary to make an application run. See the
`docs/` folder or the [wiki](https://github.com/graste/environaut/wiki) for more
information.

When you plan to use Environaut to create settings files for your application
please skip to the [Requirements and installation](#requirements-and-installation) section.

## Quickstart for curious users

1. Download the [`environaut.phar`](https://raw.github.com/graste/environaut/master/bin/environaut.phar) file of the release you prefer
1. Make it executable via `chmod u+x environaut.phar`
1. Run `./environaut.phar check`
1. Notice the error message for a missing configuration file
1. Create a minimal config file (e.g. as
   [xml](docs/examples/minimal.example.xml) or
   [json](docs/examples/minimal.example.json)) next to the `environaut.phar` file.
1. Run `./environaut.phar check` again (with ```--config ...``` if the file is not named ```environaut.(xml|json|php)```)
1. Notice the output and created settings and cache file that is used when you
   run Environaut another time.

## Short introduction for interested developers

Environaut parses a configuration file that contains defined environment checks.
After that, each check is processed and the results of each check are compiled
into a report. Each check can emit messages and settings. The messages will be
printed to the shell and the settings can be exported in specified formats.

For a verbose example configuration file do this:

1. Clone this repository and change into that directory
1. Run ```make install-dependencies-dev```
1. Run ```bin/environaut check```

Notice the checks and questions and that there's an ```environaut-config.xml```
afterwards in your working directory. Change the sample ```environaut.xml```
to use ```environaut-config.json``` instead of the XML variant as the settings
export formatter and re-run the checks to get your settings as JSON.

## Requirements and installation

- Non-Windows operating system (tested on Ubuntu 12.04/13.04 and MacOS X)
- PHP v5.3+
- `libxml` when XML configuration files are used

There are _multiple ways_ to use and run environaut:

- download and use the PHAR file
- install via Composer CLI
- install via Composer (`composer.json`)

### PHAR

The easiest way to use environaut is to download the `environaut.phar` of the
latest stable release, make it executable and put a configuration file next to
the php archive and then run ```./environaut.phar check```

### Composer CLI

Install Environaut via [Composer](http://getcomposer.org/):

1. Install Composer (if it's not already installed or available via PATH
   environment): ```curl -sS https://getcomposer.org/installer | php```.
1. Install Environaut: ```./composer.phar require graste/environaut [optional version]```
1. Create a Environaut configuration file (see [environaut.xml](environaut.xml)
   or [examples](docs/examples/))
1. Run it: ```./vendor/bin/environaut.phar check```

### Composer project vendor dependency

Another way to install `Environaut` is via [composer](http://getcomposer.org) as
a vendor dependency of your project. Create or update a `composer.json` file
and run the `php composer.phar install` command to get Environaut:

```json
{
    "require": {
        "graste/environaut": "~0.5"
    }
}
```

Alternatively, you can download the
[`environaut.zip`](https://github.com/graste/environaut/archive/master.zip)
file and extract it. The `bin/environaut.phar` file is a standalone and
self-executable binary.

    $ environaut.phar --help

When you got `environaut.phar` by cloning this repository it should already be
executable. Otherwise `chmod u+x environaut.phar` should be sufficient to
make it work. It is advisable to have a `php` executable available via the
`PATH` environment variable as the phar uses a `#!/usr/bin/env php` shebang.
Use something like ```alias php="/usr/local/bin/php53"``` if your executable
is not in the PATH. To get the `Makefile` working you can try a simple
```export PHP_PATH = "/usr/local/bin/php53"``` as that will be used instead of
the default `php` for `make`.

## Usage examples

You may combine multiple commandline options:

    environaut.phar help check
    environaut.phar check --verbose --profile
    environaut.phar check --config path/to/environaut.json
    environaut.phar check --autoload-dir path/to/custom/files/src
    environaut.phar check --no-cache

The `check` commandline options are:

- ```--autoload-dir="…" (-a)```: Folder for autoloading of custom `.php` classes.
- ```--config="…" (-c)```: Path to configuration file with check definitions.
- ```--config-handler="…"```: Namespaced classname of custom `IConfigHandler`
                              (will be autoloaded from the ```autoload_dir```).
- ```--include-path="…" (-i)```: Path to prepend to PHP ```include_path```.
- ```--bootstrap="…" (-b)```: File to require before running the checks.
- ```--no-cache```: Don't read, write or use cache files (Disables caching).
- ```--cache-location="…"```: Read and write cache from and to that file.

Other available and useful options are:

- `--verbose (-v)`: Increase the verbosity of messages.
- `--version (-V)`:` Display Environaut version.
- `--ansi`: Force ANSI output.
- `--no-ansi` Disable ANSI output.
- `--profile` Display timing and memory usage information.

For the help command the following works:

- `--xml`: To output help as XML.
- `--format`: To output help in other formats.
- `--raw`: To output raw command help.
- `--help (-h)`: Display the help message.

## Documentation

Checks can be configured via configuration files and settings may
afterwards be exported via different formatters into different formats.

Supported (input) configuration file formats:

- `XML`
- `JSON`
- `PHP`

Supported (output) settings file formats:

- `XML` (agavi xml config format; can be customized via template strings)
- `JSON` (json object literal)
- `PHP` (settings as array to include)
- `SH` (shell variables in a file that may be sourced)
- `TEXT` (settings with or without their group name in a plain text file)

The input and output file formats may be completely customized by replacing
the default classes with custom implementations.

* [more detailed documentation](docs/)
* [API docs](http://graste.github.io/environaut/docs/api/)
* [code coverage report](http://graste.github.io/environaut/build/reports/coverage-html/)

## Community

None, but you may join the freenode IRC [`#honeybee`](irc://irc.freenode.org/honeybee) channel anytime. :-)

Please contribute by [forking](http://help.github.com/forking/) and sending a
[pull request](http://help.github.com/pull-requests/). More information can be
found in the [`CONTRIBUTING.md`](CONTRIBUTING.md) file.

## Contributors

See [`AUTHORS.md`](AUTHORS.md) for a list of contributors.

## Changelog

See [`CHANGELOG.md`](CHANGELOG.md) for more information about changes.

## License

MIT license – see [linked license](LICENSE.md) for details.

* Total Composer Downloads: [![Composer Downloads](https://poser.pugx.org/graste/environaut/d/total.png)](https://packagist.org/packages/graste/environaut)
