#!/bin/bash

composer install
php bin/console doctrine:database:create --env=test --if-not-exists
php bin/console doctrine:schema:drop --full-database --env=test -f
php bin/console doctrine:schema:update --env=test --force
php bin/console mush:migrate --dev
