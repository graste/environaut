# Exports

Environaut outputs messages from configured export formatters on the CLI. The
formatters itself write files with settings from your checks.

## Supported formats

`Environaut` currently supports settings writers for the following formats:

- `XML` file via [`XmlSettingsWriter`](../src/Environaut/Export/Formatter/XmlSettingsWriter.php)
- `JSON` file via [`JsonSettingsWriter`](../src/Environaut/Export/Formatter/JsonSettingsWriter.php)
- `PHP` file via [`PhpSettingsWriter`](../src/Environaut/Export/Formatter/PhpSettingsWriter.php)
- `SHELL` file via [`ShellSettingsWriter`](../src/Environaut/Export/Formatter/ShellSettingsWriter.php)

This means, that the settings your checks emit can be exported into the above
formats to be included by your programs.

By defining multiple export formatters in your Environaut configuration file (and
the groups that settings can have) you are able to e.g. ask the user for input and
put that into configuration files that in turn can be read by your applications.

See the [`environaut.xml`](../environaut.xml#L43) configuration file example and
have a look at the different formatters defined. Each one of those can export to
another file in another format. Additional parameters for pretty printing, nesting,
templates etc. are available. For more information have a look at the class docs.

By default all settings will be exported by a formatter. When you specify a `groups`
parameter with one or more group names only settings that have that group will be
exported by that formatter.
