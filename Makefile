ifdef PHP_PATH
	PHP=$(PHP_PATH)
else
	PHP=php
endif

help:

	@echo ""
	@echo "Possible targets:"
	@echo "  install-composer - install composer"
	@echo "  install-dependencies-dev - install composer if necessary and install or update all vendor libraries (including --dev)"
	@echo "  phar - create PHP archive bin/environaut.phar"
	@echo "  tests - run all tests"
	@echo ""
	@echo "Please make sure a 'php' executable is available via PATH environment variable or set a PHP_PATH variable directly with a path like /usr/bin/php."
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

	@bin/phpunit tests/

.PHONY: tests help phar install-composer install-dependencies-dev

# vim: ts=4:sw=4:noexpandtab:
