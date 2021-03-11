#!/bin/bash

composer install
php bin/console doctrine:schema:drop -f
php bin/console doctrine:schema:update -f
yes | php bin/console doctrine:fixture:load
