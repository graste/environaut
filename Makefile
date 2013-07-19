ifdef PHP_PATH
	PHP=$(PHP_PATH)
else
	PHP=php
endif

help:

	@echo ""
	@echo "Possible targets:"
	@echo "  docs - generate API documentation into 'docs/api' folder"
	@echo "  install-composer - download and install composer to 'bin/composer.phar'"
	@echo "  install-dependencies-dev - install composer if necessary and install or update all vendor libraries (including --dev)"
	@echo "  tests - run all tests and create test coverage in 'build/reports"
	@echo "  phar - create PHP archive file 'bin/environaut.phar' (PHP ini setting 'phar.readonly' must be disabled)"
	@echo ""
	@echo "Please make sure a 'php' executable is available via PATH environment variable or set a PHP_PATH variable directly with a path like '/usr/bin/php'."
	@echo ""
	@exit 0

phar:

	@./bin/compile

install-composer:

	@if [ ! -d ./bin ]; then mkdir bin; fi
	@if [ ! -f ./bin/composer.phar ]; then curl -sS http://getcomposer.org/installer | $(PHP) -d apc.enable_cli=0 -d allow_url_fopen=1 -d date.timezone="Europe/Berlin" -- --install-dir=./bin/; fi

install-dependencies-dev:

	@make install-composer
	@$(PHP) -d apc.enable_cli=0 -d allow_url_fopen=1 -d date.timezone="Europe/Berlin" ./bin/composer.phar -- update

tests:

	@vendor/bin/phpunit tests/

docs:

	@if [ -d ./docs/api ]; then rm -rf ./docs/api; fi
	@$(PHP) vendor/bin/sami.php update ./bin/sami.cfg

.PHONY: tests docs help phar install-composer install-dependencies-dev

# vim: ts=4:sw=4:noexpandtab:
