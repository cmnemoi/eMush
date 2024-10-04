#!/bin/bash

set -e
set -o pipefail

NODE_VERSION=20
NVM_VERSION=0.39.7
POSTGRES_VERSION=15
PHP_VERSION=8.3

# Function to log messages
log_message() {
    echo "$1"
    echo "$1" >> install.log
}

# Function to run commands with logging
run_command() {
    log_message "Running: $1"
    eval "$1" >> install.log 2>&1
}

# Function to check for sudo permissions
check_sudo() {
    log_message "This script requires sudo permissions to install dependencies. Do you want to continue? (y/n)"
    read -r response
    if [ "$response" != "y" ]; then
        log_message "Exiting..."
        exit 1
    fi
    log_message "Thank you. Please provide your password when prompted."
}

# Function to update system
update_system() {
    log_message "Updating system..."
    run_command "sudo apt-get update -yq && sudo apt-get upgrade -yq"
}

# Function to install and setup PostgreSQL
install_postgres() {
    log_message "Setup PostgreSQL repositories..."
    run_command "sudo apt-get install -y postgresql-common"
    run_command "sudo /usr/share/postgresql-common/pgdg/apt.postgresql.org.sh -y"

    log_message "Installing PostgreSQL..."
    run_command "sudo apt-get update -yq && sudo apt-get install postgresql-${POSTGRES_VERSION} -yq"

    log_message "Starting PostgreSQL..."
    run_command "sudo service postgresql start"

    log_message "Creating users and databases..."
    sudo -u postgres psql -v ON_ERROR_STOP=1 --username "postgres" <<-EOSQL
        CREATE USER "mysql" WITH PASSWORD 'password' CREATEDB LOGIN;
        CREATE DATABASE "etwin.dev" WITH OWNER "mysql";
        GRANT ALL PRIVILEGES ON DATABASE "etwin.dev" TO "mysql";
        ALTER SCHEMA public OWNER TO "mysql";
EOSQL
}

# Function to install front-end dependencies
install_frontend() {
    log_message "Installing nvm ${NVM_VERSION}..."
    run_command "sudo apt-get install curl -yq"
    run_command "curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v${NVM_VERSION}/install.sh | bash"

    log_message "Loading nvm..."
    export NVM_DIR="$([ -z "${XDG_CONFIG_HOME-}" ] && printf %s "${HOME}/.nvm" || printf %s "${XDG_CONFIG_HOME}/nvm")"
    [ -s "$NVM_DIR/nvm.sh" ] && \. "$NVM_DIR/nvm.sh"

    log_message "Installing Node ${NODE_VERSION}..."
    run_command "nvm install ${NODE_VERSION}"
    run_command "nvm use ${NODE_VERSION}"

    log_message "Installing Yarn..."
    run_command "cd App && npm install -g yarn"

    log_message "Setup front-end env variables..."
    run_command "cd App && cp .env.bare-metal .env"

    log_message "Installing front-end dependencies..."
    run_command "cd App && yarn install"
}

# Function to install Eternaltwin server
install_eternaltwin() {
    log_message "Setup Eternaltwin env variables..."
    run_command "cd EternalTwin && cp etwin.bare-metal.toml.example etwin.toml"

    log_message "Installing Eternaltwin server dependencies..."
    run_command "cd EternalTwin && yarn set version latest && yarn install"
}

# Function to install back-end dependencies
install_backend() {
    log_message "Installing PHP build dependencies..."
    run_command "sudo apt-get install ca-certificates apt-transport-https software-properties-common lsb-release openssl zip unzip -yq"

    log_message "Setup PHP repositories..."
    run_command "curl -sSL https://packages.sury.org/php/README.txt | sudo bash -x"
    run_command "sudo sh -c 'echo \"deb https://packages.sury.org/php/ $(lsb_release -sc) main\" > /etc/apt/sources.list.d/php.list'"
    run_command "sudo apt-get update -yq && sudo apt-get upgrade -yq"

    log_message "Installing PHP ${PHP_VERSION}..."
    run_command "sudo apt-get install php${PHP_VERSION} -yq"

    log_message "Installing PHP extensions..."
    run_command "sudo apt-get install php${PHP_VERSION}-common php${PHP_VERSION}-pgsql php${PHP_VERSION}-curl php${PHP_VERSION}-opcache php${PHP_VERSION}-intl php${PHP_VERSION}-xml php${PHP_VERSION}-dom php${PHP_VERSION}-zip -yq"

    log_message "Installing Composer..."
    run_command "curl -sS https://getcomposer.org/installer | php"
    run_command "sudo mv composer.phar /usr/local/bin/composer"

    log_message "Creating JWT certificates..."
    run_command "cd Api && openssl genpkey -pass pass:mush -out config/jwt/private.pem -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096"
    run_command "cd Api && openssl pkey -passin pass:mush -in config/jwt/private.pem -out config/jwt/public.pem -pubout"
    run_command "cd Api && chmod go+r config/jwt/private.pem"

    log_message "Setup back-end env variables..."
    run_command "cd Api && cp .env.bare-metal .env.local"
    run_command "cd Api && cp .env.bare-metal.test .env.test.local"

    log_message "Installing back-end dependencies..."
    run_command "cd Api && composer install"
    run_command "cd Api && composer reset"
}

# Function to launch the project
launch_project() {
    log_message "Starting back-end server..."
    run_command "php -S localhost:8080 -t Api/public > /dev/null 2>&1 &"

    log_message "Starting front-end server..."
    run_command "cd App && yarn dev > /dev/null 2>&1 &"

    log_message "Starting Eternaltwin server..."
    run_command "cd EternalTwin && yarn etwin db create"
    run_command "cd EternalTwin && yarn etwin start > /dev/null 2>&1 &"
    sleep 10

    log_message "Create Eternaltwin accounts..."
    run_command "php Api/bin/console mush:create-crew"

    log_message "Filling a Daedalus with players..."
    run_command "php Api/bin/console mush:fill-daedalus"

    log_message "Project installed successfully! You can access it at http://localhost:5173"
    log_message "Use the following credentials to login:"
    log_message "Username: chun"
    log_message "Password: 1234567891"
}

# Main installation process
main() {
    check_sudo
    update_system
    install_postgres
    install_frontend
    install_eternaltwin
    install_backend
    launch_project
}

# Run the main installation process
main
