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
if  [[ $1 = "--init" ]]; then
    php bin/console doctrine:schema:drop --full-database -f
    php bin/console mush:migrate --dev
else
    php bin/console doctrine:migrations:diff --no-interaction
    php bin/console mush:migrate --dev
fi
