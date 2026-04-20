.PHONY: install analyse test cs-fixer all

DOCKER_EXEC = docker compose -f docker/docker-compose.yaml exec sdk

install:
	$(DOCKER_EXEC) composer install

analyse:
	$(DOCKER_EXEC) vendor/bin/phpstan analyse

test:
	$(DOCKER_EXEC) vendor/bin/phpunit

cs-fixer:
	$(DOCKER_EXEC) vendor/bin/php-cs-fixer fix --allow-risky=yes

all: analyse test cs-fixer ## Run all checks (analyse, test, cs-fix)
