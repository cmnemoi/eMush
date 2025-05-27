#!/bin/bash

set -e
set -o pipefail

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

# Function to detect OS
detect_os() {
    if [[ "$OSTYPE" == "linux-gnu"* ]]; then
        if [ -f /etc/debian_version ]; then
            echo "debian"
        elif [ -f /etc/arch-release ]; then
            echo "arch"
        else
            echo "unsupported"
        fi
    else
        echo "unsupported"
    fi
}

# Function to install packages at OS level
install_package() {
    local package_name="$1"
    local os_type=$(detect_os)

    case $os_type in
        debian)
            run_command "sudo apt-get install -y $package_name"
            ;;
        arch)
            run_command "sudo pacman -S --noconfirm $package_name"
            ;;
        *)
            log_message "Unsupported operating system. Only Debian-based and Arch-based systems are supported for now."
            exit 1
            ;;
    esac
}

# Function to update system
update_system() {
    local os_type=$(detect_os)

    case $os_type in
        debian)
            log_message "Updating system..."
            run_command "sudo apt-get update -y && sudo apt-get upgrade -y"
            ;;
        arch)
            log_message "Updating system..."
            run_command "sudo pacman -Syu --noconfirm"
            ;;
        *)
            log_message "Unsupported operating system. Currently only Debian-based and Arch-based systems are supported."
            exit 1
            ;;
    esac
}

# Function to install Docker dependencies
install_docker_dependencies() {
    log_message "Installing Docker dependencies..."
    update_system
    install_package "build-essential"
    install_package "curl"
    install_package "git"
}

# Function to install Docker
install_docker() {
    log_message "Installing Docker..."
    run_command "install -m 0755 -d /etc/apt/keyrings"
    run_command "sudo curl -fsSL https://download.docker.com/linux/ubuntu/gpg -o /etc/apt/keyrings/docker.asc"
    run_command "sudo chmod a+r /etc/apt/keyrings/docker.asc"
    run_command "sudo echo \"deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.asc] https://download.docker.com/linux/ubuntu $(. /etc/os-release && echo \"$VERSION_CODENAME\") stable\" | sudo tee /etc/apt/sources.list.d/docker.list > /dev/null"
    run_command "sleep 5"
    update_system
    install_package "docker-ce"
    install_package "docker-ce-cli"
    install_package "containerd.io"
    install_package "docker-buildx-plugin"
    install_package "docker-compose-plugin"
}

# Function to add user to Docker group
# This ensures the user can run Docker commands without sudo
add_user_to_docker_group() {
    log_message "Checking Docker group..."
    # Check if docker group exists
    if ! getent group docker >/dev/null 2>&1; then
        log_message "Docker group does not exist. Creating it..."
        run_command "sudo groupadd docker"
        log_message "Adding user to Docker group..."
        run_command "sudo usermod -aG docker $USER"
        run_command "newgrp docker"  # Apply new group membership without logout
    else
        # If group exists, check if user is already a member
        if ! getent group docker | grep -q "\b${USER}\b"; then
            log_message "Adding user to Docker group..."
            run_command "sudo usermod -aG docker $USER"
            run_command "newgrp docker"  # Apply new group membership without logout
        else
            log_message "User already belongs to Docker group"
        fi
    fi
}

setup-git-hooks() {
    chmod +x hooks/pre-commit
    chmod +x hooks/pre-push
    git config core.hooksPath hooks
}

setup-env-variables() {
    cp ./Api/.env.dist ./Api/.env.local
    cp ./Api/.env.test ./Api/.env.test.local
    cp ./App/.env.dist ./App/.env
    cp ./Eternaltwin/eternaltwin.toml ./Eternaltwin/eternaltwin.local.toml
}

build-docker-images() {
    docker compose -f docker/docker-compose.yml -f docker/docker-compose.dev.yml build
    docker compose -f docker/docker-compose.yml -f docker/docker-compose.dev.yml run -T -u root mush_front chown -R node:node /www
    docker compose -f docker/docker-compose.yml -f docker/docker-compose.dev.yml run -T -u root mush_eternaltwin chown -R node:node /www
    docker compose -f docker/docker-compose.yml -f docker/docker-compose.dev.yml run -T -u root mush_php chown -R dev:dev /www
    docker compose -f docker/docker-compose.yml -f docker/docker-compose.dev.yml up --no-start --remove-orphans
}

install-api() {
    docker compose -f docker/docker-compose.yml run -T -u dev mush_php composer install &&\
    docker compose -f docker/docker-compose.yml run -T -u dev mush_php ./reset.sh --init
}

install-front() {
    docker compose -f docker/docker-compose.yml run -T -u node mush_front yarn install &&\
    docker compose -f docker/docker-compose.yml run -T -u node mush_front ./reset.sh
}

install-eternaltwin() {
    docker compose -f docker/docker-compose.yml run -T -u node mush_eternaltwin yarn install
    docker compose -f docker/docker-compose.yml run -T -u node mush_eternaltwin yarn etwin db reset
    docker compose -f docker/docker-compose.yml run -T -u node mush_eternaltwin yarn etwin db sync
}

setup-JWT-certificates() {
    docker compose -f docker/docker-compose.yml run -T -u dev mush_php openssl genpkey -pass pass:mush -out config/jwt/private.pem -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096
	docker compose -f docker/docker-compose.yml run -T -u dev mush_php openssl pkey -passin pass:mush -in config/jwt/private.pem -out config/jwt/public.pem -pubout
	docker compose -f docker/docker-compose.yml run -T -u dev mush_php chmod go+r config/jwt/private.pem
}

reset-eternaltwin-database() {
    docker compose -f docker/docker-compose.yml run -T -u node mush_eternaltwin yarn etwin db reset
    docker compose -f docker/docker-compose.yml run -T -u node mush_eternaltwin yarn etwin db sync
}

start-docker-containers() {
    docker compose -f docker/docker-compose.yml up -d --no-recreate --remove-orphans
}

sleep-ten-seconds() {
    sleep 10
}

fill-daedalus() {
    docker compose -f docker/docker-compose.yml run -T -u dev mush_php php bin/console mush:fill-daedalus
}

# Function to install project
install_project() {
    log_message "Installing project..."
    setup-git-hooks
    setup-env-variables
    build-docker-images
    install-api
    install-front
    install-eternaltwin
    setup-JWT-certificates
    reset-eternaltwin-database
    start-docker-containers
    sleep-ten-seconds
    fill-daedalus
    echo "Installation completed successfully ! You can access it at http://localhost/."
    echo "Use the following credentials to login:"
    echo "username: andie"
    echo "password: 1234567891"
}

run() {
    install_docker_dependencies
    install_docker
    add_user_to_docker_group
    install_project
}

run