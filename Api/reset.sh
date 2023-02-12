#!/bin/bash

function generate-test-database
{
    echo 'Creating a database for functional tests...'
    php bin/console doctrine:database:create --env=test --if-not-exists
    php bin/console doctrine:schema:drop --full-database --env=test -f
    php bin/console doctrine:schema:update --env=test -f
}

function generate-new-migration
{
    echo 'Generating a new migration...'
    rm -rf ./migrations/*
    php bin/console doctrine:schema:drop --full-database -f
    php bin/console doctrine:migrations:diff
}

composer install
generate-test-database
generate-new-migration
php bin/console mush:migrate --dev
