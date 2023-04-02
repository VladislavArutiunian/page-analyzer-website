PORT ?= 8000
start:
	php -S 0.0.0.0:$(PORT) -t public

lint:
	composer exec --verbose phpcs -- --standard=PSR12 public/ src/ webroot/

lint-fix:
	composer exec --verbose phpcbf -- --standard=PSR12 public/ src/ webroot/

start-locally:
	php -S localhost:8080 -t public public/index.php
