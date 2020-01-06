
install: composer vendor optimize

.SILENT: composer vendor optimize

composer:
	echo -n "Installing 'composer'..\n"
	@php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
	@php -r "if (hash_file('sha384', 'composer-setup.php') === 'baf1608c33254d00611ac1705c1d9958c817a1a33bce370c0595974b342601bd80b92a3f46067da89e3b06bff421f182') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
	@php composer-setup.php
	@php -r "unlink('composer-setup.php');"

vendor:
	echo -n "Installing dependencies..\n"
	@php composer.phar install
	echo -n "ftp2mail installed.\n"

optimize:
	@php composer.phar dumpautoload -o
	@php -r "unlink('composer.phar');"