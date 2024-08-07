.DEFAULT_GOAL:=help

.PHONY: dependencies
dependencies:
	composer install --no-interaction --no-suggest --no-scripts --ansi

.PHONY: test
test:
	tests/run.sh all

.PHONY: php-cs-fixer
php-cs-fixer:
	vendor/bin/php-cs-fixer fix --no-interaction --allow-risky=yes --diff --verbose

PHONY: phpstan
phpstan:
	vendor/bin/phpstan analyse --level=6 src tests

help:
	@awk 'BEGIN {FS = ":.*##"; printf "\nUsage:\n  make \033[36m<target>\033[0m\n\nTargets:\n"} /^[a-zA-Z_-]+:.*?##/ { printf "  \033[36m%-20s\033[0m %s\n", $$1, $$2 }' $(MAKEFILE_LIST)