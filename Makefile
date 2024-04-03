build:			## Build an image from a docker-compose file.
	docker-compose -f tests/docker-compose.yml up -d --build
coverage:		## Run code coverage.
	docker-compose -f tests/docker-compose.yml run php-cli vendor/bin/phpunit --coverage-clover /app/tests/runtime/coverage.xml
test:			## Run tests.
	make build
	docker-compose -f tests/docker-compose.yml run php-cli vendor/bin/phpunit --debug
