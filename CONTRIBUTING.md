# Contributing

Your input and contributions are very welcome! Please open issues with
improvements, feature requests or bug reports.

If you want to contribute source code, add documentation or just fix spelling
mistakes try this:

1. [Fork](http://help.github.com/forking/) the current development branch (that is, usually NOT `master`).
1. Install vendor libraries needed for testing etc. via `make install-dependencies-dev`.
1. Make your changes and additions.
1. Verify your changes by making sure that `make tests` and `make codesniffer-cli` do not fail.
1. Add, commit and push the changes to your forked repository.
1. Send a [pull request](http://help.github.com/pull-requests/) to Environaut with a well written issue describing the change and why it is necessary.

Please note, that the code tries to adhere to the [PSR-2 Coding Style Guide](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md).
Commits are continously integrated via [TravisCI](https://travis-ci.org/graste/environaut)
and failing the PHPUnit or PHP CodeSniffer tests will fail the builds. Usually
the build status will be shown on your pull request by Github. If something
fails please try to fix your changes as otherwise they can't simply be incorporated.
