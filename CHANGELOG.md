# Changelog

All new features, changes and fixes should be listed here. Please use tickets
to reference changes.

## 0.2.0 (2013/xx/xx)

* [new] added builds via TravisCI for PHP v5.3, v5.4 and v5.5 (thanks!)
* [new] added initial documentation of checks in `docs/checks.md`
* [new] added `ComposerSecurityCheck` to check `composer.lock` files for known security vulnerabilities
* [new] added tests for `CheckCommand`, `Configurator`, `Validator`  and `Application` (using PHPUnit)
* [new] added reading of configuration from external files called `environaut.json` (`php` working and `.xml` still missing)
* [new] added `Configurator` check to ask for users for settings (value input, selection, confirmation and hidden input with autocompletion and validation)
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
