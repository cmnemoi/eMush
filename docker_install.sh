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
    run_command "curl -fsSL https://download.docker.com/linux/ubuntu/gpg -o /etc/apt/keyrings/docker.asc"
    run_command "chmod a+r /etc/apt/keyrings/docker.asc"
    run_command "echo \"deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.asc] https://download.docker.com/linux/ubuntu $(. /etc/os-release && echo \"$VERSION_CODENAME\") stable\" | sudo tee /etc/apt/sources.list.d/docker.list > /dev/null"
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

# Function to install project
install_project() {
    log_message "Installing project..."
    make install
}

run() {
    install_docker_dependencies
    install_docker
    add_user_to_docker_group
    install_project
}

run