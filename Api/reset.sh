#!/bin/bash

FIRST_DAEDALUS_NAME=$1

composer install
php bin/console doctrine:schema:drop -f
php bin/console doctrine:schema:update -f
php bin/console doctrine:database:create --env=test --if-not-exists
php bin/console doctrine:schema:update --env=test --force
yes | php bin/console doctrine:fixture:load
php bin/console mush:create-daedalus $FIRST_DAEDALUS_NAME


