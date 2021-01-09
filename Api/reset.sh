#!/bin/bash

composer install
bin/console doctrine:schema:drop -f
bin/console doctrine:schema:update -f
yes | bin/console doctrine:fixture:load
