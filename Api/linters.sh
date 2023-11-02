#!/bin/bash

PHP_CS_FIXER_IGNORE_ENV=1 vendor/bin/php-cs-fixer fix --dry-run --diff
if [ $? -ne 0 ];
then
  echo -e "\e[01;31m Please run: vendor/bin/php-cs-fixer fix \e[0m"
  exit 1;
fi
vendor/bin/psalm
if [ $? -ne 0 ];
then
  echo -e "\e[01;31m Please fix psalm errors : vendor/bin/psalm \e[0m"
  exit 1;
fi
vendor/bin/phpmd src text phpmd.xml --exclude "*src/*/DataFixtures/*, *src/*/*/ConfigData/*" --ignore-errors-on-exit
if [ $? -ne 0 ];
then
  echo -e "\e[01;31m Please fix phpmd errors : vendor/bin/phpmd src text phpmd.xml --exclude "*src/*/DataFixtures/*", "*src/*/*/ConfigData/*" --ignore-errors-on-exit \e[0m"
  exit 1;
fi

echo -e "\e[2;38;5;82m The code is ready to be merged \e[0m"