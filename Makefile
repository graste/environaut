
help:

	@echo "Possible targets:"
	@echo "  install-composer - install composer"
	@echo "  install-dependencies-dev - install composer if necessary and install or update all vendor libraries (including --dev)"
	@exit 0

install-composer:

	@if [ ! -d ./bin ]; then mkdir bin; fi
	@if [ ! -f ./bin/composer.phar ]; then curl -sS http://getcomposer.org/installer | php -d apc.enable_cli=0 -d allow_url_fopen=1 -d date.timezone="Europe/Berlin" -- --install-dir=./bin/; fi

install-dependencies-dev:

	@make install-composer
	@php -d apc.enable_cli=0 -d allow_url_fopen=1 -d date.timezone="Europe/Berlin" ./bin/composer.phar -- update --dev
	
.PHONY: test help

# vim: ts=4:sw=4:noexpandtab!:
