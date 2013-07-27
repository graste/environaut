# Environaut

* Version: 0.2.0-dev
* Date: 2013/xx/xx
* Build: [![Build Status](https://secure.travis-ci.org/graste/environaut.png?branch=0.2.0)](http://travis-ci.org/graste/environaut)

## Purpose

`Environaut` should enable and help developers to define the environment of an
application and check if all defined constraints are met. This includes
assertions and requirements of the application environment as well as some
configuration that may be necessary to make an application run. See the
[wiki](https://github.com/graste/environaut/wiki) for more information.

## Quickstart

Environaut parses a configuration file that contains defined environment checks.
After that each check is processed and the results of each check are compiled
into a report. Each check can emit messages and settings. The messages will be
printed to the shell and the settings can be exported in specified formats.

1. Clone this repository and change into that directory
2. Run ```make install-dependencies-dev```
3. Run ```bin/environaut check```

Notice the checks and questions and that there's an ```environaut-config.xml```
afterwards in your working directory. Change the sample ```environaut.xml```
to use ```environaut-config.json``` instead of the XML variant as the settings
export formatter and re-run the checks to get your settings as JSON.

## Requirements and installation

- Non-Windows operating system (tested on Ubuntu 12.04/13.04 and MacOS X)
- PHP v5.3+

If you just want to configure and run `Environaut`, you need to download the
`bin/environaut.phar` binary. The file is a standalone and self-executable file.

    $ environaut.phar --help

When you got `environaut.phar` by cloning this repository it should already be
executable. Otherwise just `chmod u+x environaut.phar` should be sufficient to
make it work. It is advisable to have a `php` executable available via the
`PATH` environment variable as the phar uses a `#!/usr/bin/env php` shebang.
Use something like ```alias php="/usr/local/bin/php53"``` if your executable
is not in the PATH. To get the `Makefile` working you can try a simple
```export PHP_PATH = "/usr/local/bin/php53"``` as that will be used instead of
the default `php` for `make`.

Another way to install `Environaut` is via [composer](http://getcomposer.org).
Just create a `composer.json` file and run the `php composer.phar install`
command to install it:

```json
{
    "require": {
        "graste/environaut": "~0.2.0"
    }
}
```

Alternatively, you can download the [`environaut.zip`][1] file and extract it.

## Usage examples

You may combine multiple commandline options:

    environaut.phar help check
    environaut.phar check --verbose --profile
    environaut.phar check --config path/to/environaut.json
    environaut.phar check --autoload_dir path/to/custom/files/src

The `check` commandline options are:

- ```--autoload_dir="…" (-a)```: Folder for autoloading of custom `.php` classes.
- ```--config="…" (-c)```: Path to configuration file with check definitions.
- ```--config_handler="…"```: Namespaced classname of custom `IConfigHandler`
                              (will be autoloaded from the ```autoload_dir```).
- ```--include_path="…" (-i)```: Path to prepend to PHP ```include_path```.
- ```--bootstrap="…" (-b)```: File to require before running the checks.

Other available and useful options are:

- `--verbose (-v)`: Increase the verbosity of messages.
- `--version (-V)`:` Display Environaut version.
- `--ansi`: Force ANSI output.
- `--no-ansi` Disable ANSI output.
- `--profile` Display timing and memory usage information

For the help command the following works:

- `--xml`: To output help as XML.
- `--format`: To output help in other formats.
- `--raw`: To output raw command help.
- `--help (-h)`: Display the help message.

## Documentation

tbd. topics?

## Community

None, but you may join the freenode IRC [`#environaut`](irc://irc.freenode.org/environaut) channel anytime. :-)

Please contribute by [forking](http://help.github.com/forking/) and sending a
[pull request](http://help.github.com/pull-requests/). More information may be
found in the [`CONTRIBUTING.md`](CONTRIBUTING.md) file.

## Contributors

See [`AUTHORS.md`](AUTHORS.md) for a list of contributors.

## Changelog

See [`CHANGELOG.md`](CHANGELOG.md) for more information about changes.

## License

<a rel="license" href="http://creativecommons.org/licenses/by-sa/3.0/deed.en_US"><img alt="Creative Commons License" style="border-width:0" src="http://i.creativecommons.org/l/by-sa/3.0/88x31.png" /></a><br /><span xmlns:dct="http://purl.org/dc/terms/" property="dct:title">Environaut</span> is licensed under a <a rel="license" href="http://creativecommons.org/licenses/by-sa/3.0/deed.en_US">Creative Commons Attribution-ShareAlike 3.0 Unported License</a>.

CC-BY-SA-3.0 means, you are free to share, remix and make commercial use of the
work as long as you attribute and share alike. See linked license for details.

[1]: https://github.com/graste/environaut/archive/0.2.0.zip

