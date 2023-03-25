PORT ?= 8000
start:
  PHP_CLI_SERVER_WORKERS=5 php -S 0.0.0.0:$(PORT) -t public

lint:
	composer exec --verbose phpcs -- --standard=PSR12 public

lint-fix:
	composer exec --verbose phpcbf -- --standard=PSR12 public

start-locally:
	php -S localhost:8080 -t public public/index.php
