#!/bin/sh

PHP_VERSION=8.3.7

set -e
set -x

echo "This script (install_php_debian.sh) requires sudo permissions to install dependencies. Do you want to continue? (y/n)"
echo "Note : you should probably not run random scripts from the internet without understanding what they do. I highly suggest you read the script before you answer :)"
read -r response

if [ "$response" != "y" ]; then
    echo "Exiting..."
    exit 1
fi

echo "Thank you. Please provide your password when prompted."

echo "Updating system..."
sudo apt-get update -yq >> install_php.log && sudo apt-get upgrade -yq >> install_php.log

echo "Installing PHP build dependencies..."
sudo apt-get install ca-certificates apt-transport-https software-properties-common lsb-release openssl zip unzip -yq >> install_php.log

echo "Setup PHP repositories..."
curl -sSL https://packages.sury.org/php/README.txt | sudo bash -x
sudo sh -c 'echo "deb https://packages.sury.org/php/ $(lsb_release -sc) main" > /etc/apt/sources.list.d/php.list'og
sudo apt-get update -yq >> install_php.log && sudo apt-get upgrade -yq >> install_php.log

echo "Installing PHP ${PHP_VERSION}..."
sudo apt-get install php${PHP_VERSION} -yq >> install_php.log

echo "Installing Composer..."
curl -sS https://getcomposer.org/installer | php

echo "Moving Composer to bin..."
sudo mv composer.phar /usr/local/bin/composer

echo "PHP and Composer installed successfully !"