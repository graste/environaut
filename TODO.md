# TODOs

See <https://github.com/graste/environaut/issues/7>;

- make it possible to disable exports or just run no formatters by default etc.
- add check list command instead of always running checks (maybe --dry-run
  option?)
- perhaps add check for invalid whitespace before/after ```<?php/?>```
- add export formatter for following formats:
    - markdown
    - json
    - php (done)
    - xml (done)
    - nagios plugin compatible report format?
- add token handling for dependencies (including multiple reruns)?
    - add nested checks or just delete the interface?
    - add automatic mode to executable checks when dependency management is there?
    - for dependencies or nested checks:
        - ask for value (writable directory etc.) when certain check fails
- fail check run if a check returns false? or just on errors? or at all?
- add check for accelerator (apc, eaccelerator, xcache) being present
- add simple pre-execution check for environaut requirements
    - JSON extension should be installed (```json_encode``` etc.)
    - libXML extension should be installed (DOMDocument etc.)
    - PHP version should be ```>= 5.3.2```
- think about PHP-FPM settings check and other WEB instead of CLI related checks
    - certain directories should not execute PHP files (like upload locations)
    - web server should handle and return cache headers correctly
    - are certain security related headers available (`X-Content-...` etc.)
    - aggregate some good practices for file uploading into a check?
    - ...
- think about quality assurance checks:
    - it's probably something that should be done by a CI system, but still...
    - is there a "security related" sniffer for code analysis that may be run?
    - perhaps lint common file extensions (`php -l`, `jsl`, `xmllint`...)
    - this could be a last resort or pre-flight checklist for deployments
- think about user/group/file system permissions and perhaps check directories
  and files for being non-writable with exclusions for cache, log, uploade etc.

