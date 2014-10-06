# Changelog

All new features, changes and fixes should be listed here. Please use tickets to reference changes.

## 0.5.2 (2014/10/06)

* updated composer dependencies for:
    * symfony/console
    * symfony/finder
    * security-checker
    * phpunit
    * sami
    * codesniffer

## 0.5.1 (2014/03/27)

* small doc changes

## 0.5.0 (2014/03/26)

* [new] added `PlainTextSettingsWriter` that allows to generate plain text files with settings
* [new] vksprintf now allows using dots within named arguments
* [fix] vksprintf hyphen now correctly escaped
* [fix] vksprintf implementation for numeric arguments

## 0.4.3 (2014/02/20)

* [fix] Add sensiolabs/security-checker certificate to phar file and fix version number display on CLI. No other changes.

## 0.4.2 (2014/02/10)

* [fix] Phar compilation now works again. Composer broke as their lib needs a
        file called ```autoload_psr4.php``` that is not included in environaut's
        `PharCompiler` as a necessary file. For more details see issue #15 and
        read [the post by Jordi Boggiano](http://seld.be/notes/psr-4-autoloading-support-in-composer).

## 0.4.1 (2013/10/14)

* [fix] PHP settings that are boolean `false` can be empty strings or `0` depending on how they are set. The equals
        comparison of `0` in the check config failed against those empty strings. This should work now.

* [chg] Some messages and hints have been changed. The example `environaut.xml` configuration xincludes more checks.

## 0.4.0 (2013/09/26)

* [new] added `template` parameter to `ShellSettingsWriter` and `PhpSettingsWriter` formatters
* [new] added `nested` parameter to ShellSettingsWriter`, `PhpSettingsWriter` and `JsonSettingsWriter` formatters
* [new] added class docs about supported parameters on the various formatters
* [new] added a simple introductory document about exports (see [docs/exports.md](docs/exports.md))

* [fix] default fallback `location` of the new shell settings writer now has the extension `sh` instead of `json`

* [chg] refactored `vksprintf` method from `XmlSettingsWriter` to `BaseFormatter` class

## 0.3.0 (2013/09/16)

* [new] added `ShellSettingsWriter` formatter that produces shell scripts with variable definitions

## 0.2.2 (2013/09/12)

* [fix] The multi level inclusion of Environaut XML configuration files via
  XInclude directive was not possible due to the nature of XInclude processing.
  This has been fixed by using a complex type instead of simple types for the
  simple `name`, `description` and `introduction` elements in the XSD. You can
  now XInclude configs from elsewhere that on itself XInclude other configs.
  This enables you to create more sophisticated Environaut configuration
  hierarchies and pools of checks.

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
