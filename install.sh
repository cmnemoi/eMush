#!/bin/bash

set -e

NODE_VERSION=22
NVM_VERSION=0.40.1
POSTGRES_VERSION=17
PHP_VERSION=8.5

# Function to log messages
log_message() {
    echo "$1"
    echo "$1" >> install.log
}

# Function to run commands with logging
run_command() {
    log_message "Running: $1"
    eval "$1"
}

# Function to clean previous PID file
clean_pid_file() {
    if [ -f "./server_pids" ]; then
        rm ./server_pids
    fi
}

# Function to install packages
install_package() {
    local package_name="$1"
    log_message "Installing $package_name..."
    run_command "sudo apt-get install -y $package_name"
}

# Function to update system
update_system() {
    log_message "Updating system..."
    run_command "sudo apt-get update -y && sudo apt-get upgrade -y"
}

# Function to install and setup PostgreSQL
install_postgres() {
    log_message "Installing PostgreSQL ${POSTGRES_VERSION}..."
    install_package "postgresql-${POSTGRES_VERSION}"
    install_package "postgresql-client-common"
    install_package "postgresql-client-${POSTGRES_VERSION}"
    
    # Create cluster if it doesn't exist
    if [ ! -d "/var/lib/postgresql/${POSTGRES_VERSION}/main" ]; then
        log_message "Creating PostgreSQL cluster..."
        run_command "sudo pg_createcluster ${POSTGRES_VERSION} main"
    fi
    log_message "Starting PostgreSQL service..."
    run_command "sudo service postgresql start"

    # Wait for PostgreSQL to start
    log_message "Waiting for PostgreSQL to start..."
    sleep 5

    # Verify PostgreSQL is running
    if ! pg_isready; then
        log_message "Error: PostgreSQL failed to start"
        exit 1
    fi

    log_message "Creating users and databases..."
        
    sudo -u postgres psql -v ON_ERROR_STOP=1 --username "postgres" <<-EOSQL
        CREATE USER "mysql" WITH PASSWORD 'password';
        ALTER ROLE "mysql" CREATEDB;
        CREATE DATABASE "mush" WITH OWNER "mysql";
        GRANT ALL PRIVILEGES ON DATABASE "mush" TO "mysql";
    
        CREATE USER "etwin.dev" WITH PASSWORD 'password';
        ALTER ROLE "etwin.dev" CREATEDB;
        CREATE DATABASE "etwin.dev" WITH OWNER "etwin.dev";
        GRANT ALL PRIVILEGES ON DATABASE "etwin.dev" TO "etwin.dev";
        
        \c etwin.dev
        ALTER SCHEMA public OWNER TO "etwin.dev";
        GRANT ALL ON SCHEMA public TO "etwin.dev";
EOSQL
}

# Function to install front-end dependencies
install_frontend() {
    log_message "Installing nvm ${NVM_VERSION}..."
    install_package "curl"
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
    run_command "cp .env.bare.metal .env"

    log_message "Installing front-end dependencies..."
    run_command "yarn install"

    run_command "cd .."
}

# Function to install Eternaltwin server
install_eternaltwin() {
    log_message "Setup Eternaltwin env variables..."
    run_command "cd Eternaltwin && cp eternaltwin.bare.metal.toml eternaltwin.local.toml"

    log_message "Installing Eternaltwin server dependencies..."
    run_command "yarn set version latest && yarn install"

    log_message "Installing Eternaltwin server..."
    run_command "yarn etwin db reset && yarn etwin db sync"

    run_command "cd .."
}

# Function to install back-end dependencies
install_backend() {
    log_message "Installing PHP build dependencies..."
    install_package "ca-certificates"
    install_package "apt-transport-https"
    install_package "lsb-release"
    install_package "openssl"
    install_package "zip"
    install_package "unzip"

    log_message "Setup PHP repositories..."
    if [ "$(lsb_release -si)" == "Debian" ]; then
        run_command "curl -sSL https://packages.sury.org/php/README.txt | sudo bash -x"
        run_command "sudo apt-get update -y && sudo apt-get upgrade -y"
    fi
    if [ "$(lsb_release -si)" == "Ubuntu" ]; then
        install_package "software-properties-common"
        run_command "LC_ALL=C.UTF-8 sudo add-apt-repository ppa:ondrej/php"
        run_command "sudo apt-get update -y && sudo apt-get upgrade -y"
    fi

    log_message "Installing PHP ${PHP_VERSION}..."
    install_package "php${PHP_VERSION}"

    log_message "Installing PHP extensions..."
    install_package "php${PHP_VERSION}-common"
    install_package "php${PHP_VERSION}-pgsql"
    install_package "php${PHP_VERSION}-curl"
    install_package "php${PHP_VERSION}-intl"
    install_package "php${PHP_VERSION}-xml"
    install_package "php${PHP_VERSION}-dom"
    install_package "php${PHP_VERSION}-zip"
    install_package "php${PHP_VERSION}-mbstring"
    install_package "php${PHP_VERSION}-protobuf"

    log_message "Installing Composer..."
    run_command "curl -sS https://getcomposer.org/installer | php"
    run_command "sudo mv composer.phar /usr/local/bin/composer"

    log_message "Creating JWT certificates..."
    run_command "cd Api && openssl genpkey -pass pass:mush -out config/jwt/private.pem -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096"
    run_command "openssl pkey -passin pass:mush -in config/jwt/private.pem -out config/jwt/public.pem -pubout"
    run_command "chmod go+r config/jwt/private.pem"

    log_message "Setup back-end env variables..."
    run_command "cp .env.bare.metal .env"
    run_command "cp .env.bare.metal .env.local"
    run_command "cp .env.bare.metal.test .env.test.local"

    log_message "Installing back-end dependencies..."
    run_command "composer install"
    run_command "composer reset"
    run_command "php bin/console mush:generate-web-push-keys"
}

# Function to launch the project
launch_project() {
    log_message "Project installed successfully! You can access it by running make start."
    log_message "Use the following credentials to login:"
    log_message "Username: chun"
    log_message "Password: 1234567891"
}

# Main installation process
main() {
    update_system
    install_postgres
    install_frontend
    install_eternaltwin
    install_backend
    launch_project
}

# Run the main installation process
main
