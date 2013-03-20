# Environaut

* Version: 0.2.0-dev
* Date: 2013/xx/xx
* Build: tbd

## Purpose

`Environaut` should enable and help developers to define the environment of an application and check if all defined constraints are met. This includes assertions and requirements of the application environment as well as some configuration that may be necessary to make an application run. See the [wiki](https://github.com/graste/environaut/wiki) for more information.

## Requirements and installation

The recommended way to install `Environaut` is via [composer](http://getcomposer.org). Just create a `composer.json` file and run the `php composer.phar install` command to install it:

    {
        "require": {
            "graste/environaut": "0.2.0"
        }
    }

Alternatively, you can download the [`environaut.zip`][1] file and extract it.

## Usage examples

    bin/environaut about --profile

## Documentation

tbd

## Community

None, but you may join the freenode IRC [`#environaut`](irc://irc.freenode.org/environaut) channel anytime. :-)

Please contribute by [forking](http://help.github.com/forking/) and sending a [pull request](http://help.github.com/pull-requests/).

## Changelog

See `CHANGELOG.md` for more information about changes.

## Contributors

See `AUTHORS.md` for a list of contributors.

## License

<a rel="license" href="http://creativecommons.org/licenses/by-sa/3.0/deed.en_US"><img alt="Creative Commons License" style="border-width:0" src="http://i.creativecommons.org/l/by-sa/3.0/88x31.png" /></a><br /><span xmlns:dct="http://purl.org/dc/terms/" property="dct:title">Environaut</span> is licensed under a <a rel="license" href="http://creativecommons.org/licenses/by-sa/3.0/deed.en_US">Creative Commons Attribution-ShareAlike 3.0 Unported License</a>.

CC-BY-SA-3.0 means, you are free to share, remix and make commercial use of the work as long as you attribute and share alike. See linked license for details.

[1]: https://github.com/graste/environaut/archive/0.1.1.zip

