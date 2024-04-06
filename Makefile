build:			## Build an image from a docker-compose file.
	docker-compose -f tests/docker-compose.yml up -d --build
composer:
	docker-compose -f tests/docker-compose.yml run php-cli composer install
down :			## Stop and remove containers, networks, images, and volumes.
	docker-compose -f tests/docker-compose.yml down
coverage:		## Run code coverage.
	docker-compose -f tests/docker-compose.yml run php-cli vendor/bin/phpunit --coverage-clover /app/tests/runtime/coverage.xml
test:			## Run tests.
	make down
	make build
	docker-compose -f tests/docker-compose.yml run php-cli vendor/bin/phpunit
test-unit:		## Run unit tests.
	make down
	make build
	docker-compose -f tests/docker-compose.yml run php-cli vendor/bin/phpunit --testsuite Unit
test-integration:	## Run integration tests.
	make down
	make build
	docker-compose -f tests/docker-compose.yml run php-cli vendor/bin/phpunit --testsuite Integration
static-analyze:		## Run code static analyze.
	make build
	docker-compose -f tests/docker-compose.yml run php-cli vendor/bin/psalm --config=psalm.xml --shepherd --stats --php-version=8.1
rector:			## Run rector.
	make build
	docker-compose -f tests/docker-compose.yml run php-cli vendor/bin/rector process --config=rector.php --dry-run
mutation:		## Run mutation tests.
	make down
	make build
	docker-compose -f tests/docker-compose.yml run php-cli ./vendor/bin/roave-infection-static-analysis-plugin
