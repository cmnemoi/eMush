#!/bin/bash

function generate-test-database
{
    echo 'Creating a database for functional tests...'
    php bin/console doctrine:database:create --env=test --if-not-exists
    php bin/console doctrine:schema:drop --full-database --env=test -f
    php bin/console doctrine:schema:update --env=test -f
}

composer install
generate-test-database
php bin/console doctrine:migrations:diff --no-interaction # generating new migration here because somehow it doesn't work in mush:migrate command (it does not detecting new migration)
php bin/console mush:migrate --dev
