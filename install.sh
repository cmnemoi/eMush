#!/bin/bash

set -e
set -o pipefail

NODE_VERSION=20
NVM_VERSION=0.39.7
POSTGRES_VERSION=15
PHP_VERSION=8.3

echo "This script requires sudo permissions to install dependencies. Do you want to continue? (y/n)"
read -r response

if [ "$response" != "y" ]; then
    echo "Exiting..."
    exit 1
fi

echo "Thank you. Please provide your password when prompted."

echo "Updating system..."
sudo apt-get update -yq > install.log && sudo apt-get upgrade -yq >> install.log

##############################
#                            #
# INSTALL POSTGRES DATABASES # 
#                            #
##############################

echo "Setup PostgreSQL repositories..."
sudo apt-get install -y postgresql-common >> install.log
sudo /usr/share/postgresql-common/pgdg/apt.postgresql.org.sh -y >> install.log

echo "Installing PostgreSQL..."
sudo apt-get update -yq >> install.log && sudo apt-get install postgresql-${POSTGRES_VERSION} -yq >> install.log

echo "Starting PostgreSQL..."
sudo service postgresql start

echo "Creating users and databases..."

sudo -u postgres psql -v ON_ERROR_STOP=1 --username "postgres" <<-EOSQL
        CREATE USER "mysql" WITH PASSWORD 'password' CREATEDB LOGIN;
		CREATE DATABASE "etwin.dev" WITH OWNER "mysql";
		GRANT ALL PRIVILEGES ON DATABASE "etwin.dev" TO "mysql";
		ALTER SCHEMA public OWNER TO "mysql";
EOSQL

#####################
#                   #
# INSTALL FRONT-END # 
#                   #
#####################

echo "Installing nvm ${NVM_VERSION}..."
sudo apt-get install curl -yq >> install.log
curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v${NVM_VERSION}/install.sh | bash

echo "Loading nvm..."
export NVM_DIR="$([ -z "${XDG_CONFIG_HOME-}" ] && printf %s "${HOME}/.nvm" || printf %s "${XDG_CONFIG_HOME}/nvm")"
[ -s "$NVM_DIR/nvm.sh" ] && \. "$NVM_DIR/nvm.sh"

echo "Installing Node ${NODE_VERSION}..."
nvm install ${NODE_VERSION}
nvm use ${NODE_VERSION}

echo "Installing Yarn ${YARN_VERSION}..."
cd App
npm install -g yarn

echo "Setup front-end env variables..."
cp .env.bare-metal .env

echo "Installing front-end dependencies..."
yarn install
cd ..

##############################
#                            #
# INSTALL ETERNALTWIN SERVER # 
#                            #
##############################

echo "Setup Eternaltwin env variables..."
cd EternalTwin
cp etwin.bare-metal.toml.example etwin.toml

echo "Installing Eternaltwin server dependencies..."
yarn set version latest
yarn install
cd ..

#####################
#                   #
# INSTALL BACK-END  # 
#                   #
#####################

echo "Installing PHP build dependencies..."
sudo apt-get install ca-certificates apt-transport-https software-properties-common lsb-release openssl -yq >> install.log

echo "Setup PHP repositories..."
curl -sSL https://packages.sury.org/php/README.txt | sudo bash -x
sudo sh -c 'echo "deb https://packages.sury.org/php/ $(lsb_release -sc) main" > /etc/apt/sources.list.d/php.list'og
sudo apt-get update -yq >> install.log && sudo apt-get upgrade -yq >> install.log

echo "Installing PHP ${PHP_VERSION}..."
sudo apt-get install php${PHP_VERSION} -yq >> install.log

echo "Installing PHP extensions..."
sudo apt-get install php${PHP_VERSION}-common php${PHP_VERSION}-pgsql php${PHP_VERSION}-curl php${PHP_VERSION}-opcache \
    php${PHP_VERSION}-intl php${PHP_VERSION}-xml php${PHP_VERSION}-dom -yq >> install.log

echo "Installing Composer..."
curl -sS https://getcomposer.org/installer | php

echo "Moving Composer to bin..."
sudo mv composer.phar /usr/local/bin/composer

echo "Creating JWT certificates..."
cd Api
openssl genpkey -pass pass:mush -out config/jwt/private.pem -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096
openssl pkey -passin pass:mush -in config/jwt/private.pem -out config/jwt/public.pem -pubout
chmod go+r config/jwt/private.pem

echo "Setup back-end env variables..."
cp .env.bare-metal .env.local
cp .env.bare-metal.test .env.test.local

echo "Installing back-end dependencies..."
composer install
composer reset
cd ..

##################
#                #
# LAUNCH PROJECT # 
#                #
##################

echo "Starting back-end server..."
php -S localhost:8080 -t Api/public > /dev/null 2>&1 &

echo "Starting front-end server..."
cd App
yarn dev > /dev/null 2>&1 &
cd ..

echo "Starting Eternaltwin server..."
cd EternalTwin
yarn etwin db create
yarn etwin start > /dev/null 2>&1 &
cd ..

echo "Create Eternaltwin accounts..."
php Api/bin/console mush:create-crew

echo "Filling a Daedalus with players..."
php Api/bin/console mush:fill-daedalus

echo "Project installed successfully! You can access it at http://localhost:5173"
echo "Use the following credentials to login:"
echo "Username: chun"
echo "Password: 1234567891"