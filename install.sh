#!/bin/bash

set -e
set -x
set -o pipefail

NODE_VERSION=20
NVM_VERSION=0.39.7
POSTGRES_VERSION=14 # 15.1 is unsupported by Ubuntu 22.04
PHP_VERSION=8.3
YARN_VERSION=4.1.0

# Ask to run as sudo
echo "This script requires sudo permissions to install dependencies. Do you want to continue? (y/n)"
read -r response

if [ "$response" != "y" ]; then
    echo "Exiting..."
    exit 1
fi

echo "Thank you. Please provide your password when prompted."

##############################
#                            #
# INSTALL POSTGRES DATABASES # 
#                            #
##############################

# Install PostgreSQL
sudo apt-get install postgresql-${POSTGRES_VERSION} -yq

# Start PostgreSQL
sudo service postgresql start

# Create databases
export POSTGRES_MULTIPLE_DATABASES="emush,etwin.dev" 
export POSTGRES_USER="postgres"
bash docker/Database/install.sh

#####################
#                   #
# INSTALL FRONT-END # 
#                   #
#####################

# Install nvm
curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v${NVM_VERSION}/install.sh | bash

# Load nvm
export NVM_DIR="$([ -z "${XDG_CONFIG_HOME-}" ] && printf %s "${HOME}/.nvm" || printf %s "${XDG_CONFIG_HOME}/nvm")"
[ -s "$NVM_DIR/nvm.sh" ] && \. "$NVM_DIR/nvm.sh"

# Install node
nvm install ${NODE_VERSION}

# Install yarn
npm install -g yarn@${YARN_VERSION}

# Setup env variables
cp App/.env.dist App/.env

# Install front-end dependencies
cd App 
yarn install
cd ..

##############################
#                            #
# INSTALL ETERNALTWIN SERVER # 
#                            #
##############################

# Setup env variables
cp EternalTwin/.etwin.toml.example EternalTwin/.etwin.toml

# Install ET server dependencies
yarn install

#####################
#                   #
# INSTALL BACK-END  # 
#                   #
#####################

# Update system
sudo apt-get update -yq && sudo apt-get upgrade -yq

# Install PHP dependencies
sudo apt-get install ca-certificates apt-transport-https software-properties-common lsb-release -yq

# Add PHP repository
add-apt-repository ppa:ondrej/php -yq
sudo apt-get update -yq && sudo apt-get upgrade -yq

# Install PHP
sudo apt-get install php${PHP_VERSION} -yq

# Install PHP extensions
sudo apt-get install php${PHP_VERSION}-common php${PHP_VERSION}-pgsql php${PHP_VERSION}-curl php${PHP_VERSION}-opcache php${PHP_VERSION}-intl -yq

# Install Composer
curl -sS https://getcomposer.org/installer | php

# Move Composer to bin
mv composer.phar /usr/local/bin/composer

# Create JWT certificates
openssl genpkey -pass pass:mush -out config/jwt/private.pem -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096
openssl pkey -passin pass:mush -in config/jwt/private.pem -out config/jwt/public.pem -pubout
chmod go+r config/jwt/private.pem

# Setup env variables
cp Api/.env.bare-metal Api/.env

# Install back-end dependencies
cd Api
composer install
php bin/console mush:migrate --dev
cd ..

##################
#                #
# LAUNCH PROJECT # 
#                #
##################

# Launch back-end
php -S localhost:8080 -t Api/public

# Launch front-end
cd App
yarn dev
cd ..

# Launch Eternaltwin server
cd EternalTwin
yarn etwin db create
yarn etwin start
cd ..

echo "Project launched successfully. You can access it at http://localhost:5173"