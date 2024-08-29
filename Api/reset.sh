#!/bin/bash

function generate-test-database
{
    echo 'Creating a database for functional tests...'
    php bin/console doctrine:database:create --env=test --if-not-exists
    php bin/console doctrine:schema:drop --full-database --env=test -f
    php bin/console doctrine:schema:update --complete --env=test -f
    php bin/console doctrine:fixtures:load --env=test -n --verbose
}

composer install
generate-test-database
if  [[ $1 = "--init" ]]; then
    php bin/console doctrine:schema:drop --full-database -f
else
    php bin/console doctrine:migrations:diff --no-interaction
fi

php bin/console mush:migrate --dev
