#!/usr/bin/env bash

docker-compose exec -T php ./vendor/bin/phpcs --config-set installed_paths vendor/drupal/coder/coder_sniffer
rm -rf vendor/squizlabs/php_codesniffer
composer install --prefer-dist --no-progress
docker-compose exec -T php ./vendor/bin/phpcs --standard=Drupal --warning-severity=0 --ignore=themes/*/css profile
docker-compose exec -T php ./vendor/bin/phpcs --standard=DrupalPractice --warning-severity=0 --ignore=themes/*/css profile
