#!/bin/bash

set -e
set -o pipefail

POSTGRES_VERSION=15
PHP_VERSION=8.3

echo "Uninstalling PostgreSQL ${POSTGRES_VERSION}..."
sudo apt-get -yq purge postgresql-${POSTGRES_VERSION} > uninstall.log

echo "Removing PostgreSQL repositories..."
sudo apt-get -yq remove postgresql-common >> uninstall.log
sudo apt-get -yq autoremove >> uninstall.log

echo "Removing PostgreSQL data..."
sudo rm -rf /var/lib/postgresql
sudo rm -rf /etc/postgresql

echo "Uninstalling nvm..."
rm -rf $NVM_DIR
rm -rf ~/.npm
rm -rf ~/.bower

echo "Uninstalling PHP ${PHP_VERSION}..."
sudo add-apt-repository -y ppa:ondrej/php >> uninstall.log
sudo apt-get -yq purge php${PHP_VERSION} >> uninstall.log
sudo rm -rf /usr/local/bin/composer

echo "Done."