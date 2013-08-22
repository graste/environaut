# Changelog

All new features, changes and fixes should be listed here. Please use tickets to reference changes.

## 0.2.1 (2013/08/22)

* [fix] The autoloading was not working when Environaut was used via Composer
  JSON file in projects.

## 0.2.0 (2013/08/05)

This is the first release that may possibly be helpful. Checks can be
configured via configuration files and settings may afterwards be exported
via different formatters into XML, JSON and PHP format files. The emitted
settings from checks are cachable and may be used on subsequent runs.

New checks:

* [new] added `Configurator` check to ask for users for settings (value input, selection, confirmation and hidden input with autocompletion and validation)
* [new] added `ExecutableCheck` that adds absolute paths to executables as settings
* [new] added `PhpSettingCheck` that checks PHP ini settings for correct values
* [new] added `PhpExtensionCheck` that checks PHP extensions for being present and correctly configured
* [new] added `ComposerSecurityCheck` to check `composer.lock` files for known security vulnerabilities

Furthermore:

* [new] added caching support - checks can now emit cachable settings and reuse those on subsequent runs
* [new] added support for settings export by writing JSON, XML and PHP files via export formatters (all or just specific groups)
* [new] added support for configuration files in XML, JSON and PHP format with XML being validated via an XSD schema file
* [new] added automatic builds via TravisCI for PHP v5.3, v5.4 and v5.5 (thanks!)
* [new] added initial documentation of checks in `docs/checks.md`
* [new] added `make code-sniffer` and `make code-sniffer-cli` targets and PHP CodeSniffer support with PSR-2 coding standard checking
* [new] added `make docs` target to build API documentation via Sami to the `docs/api` folder
* [new] added tests for `CheckCommand`, `Configurator`, `Validator`  and `Application` (using PHPUnit)
* [new] added `CheckCommand` that checks the environment according to a (given) configuration
* [new] added `make phar` to create a self-executable standalone `Environaut` version named `environaut.phar`
* [new] basic infrastructure, architecture and application interfaces and implementations

* [fix] none

* [chg] the lot

## 0.1.1 (2013/03/20)

* [new] initial version with correct versioning in changelog and readme
* [fix] none
* [chg] none

## 0.1.0 (2013/xx/xx)

* [new] initial version
* [fix] none
* [chg] none
