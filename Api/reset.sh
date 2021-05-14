#!/bin/bash

composer install
php bin/console doctrine:schema:drop -f
php bin/console doctrine:schema:update -f
php php bin/console doctrine:database:create --env=test --if-not-exists
php php bin/console doctrine:schema:update --env=test --force
yes | php bin/console doctrine:fixture:load

