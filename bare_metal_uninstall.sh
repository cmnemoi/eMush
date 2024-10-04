#!/bin/bash

set -e
set -o pipefail

POSTGRES_VERSION=15
PHP_VERSION=8.3
LOG_FILE="uninstall.log"

# Logging function
log_message() {
    echo "$1"
    echo "$1" >> "$LOG_FILE"
}

# Function to run commands and log output
run_command() {
    log_message "Running: $1"
    eval "$1" >> "$LOG_FILE" 2>&1
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
    elif [[ "$OSTYPE" == "darwin"* ]]; then
        echo "macos"
    else
        echo "unsupported"
    fi
}

# Function to uninstall packages at OS level
uninstall_package() {
    local package_name="$1"
    local os_type=$(detect_os)

    case $os_type in
        debian)
            run_command "sudo apt-get remove -y $package_name"
            ;;
        arch)
            run_command "sudo pacman -R --noconfirm $package_name"
            ;;
        macos)
            if command -v brew &> /dev/null; then
                run_command "brew uninstall $package_name"
            else
                log_message "Homebrew is not installed. Cannot uninstall $package_name."
            fi
            ;;
        *)
            log_message "Unsupported operating system. Cannot uninstall $package_name."
            ;;
    esac
}


# Uninstall PostgreSQL
uninstall_postgres() {
    log_message "Uninstalling PostgreSQL ${POSTGRES_VERSION}..."
    uninstall_package "postgresql-${POSTGRES_VERSION}"
    
    log_message "Removing PostgreSQL repositories..."
    uninstall_package "postgresql-common"
    if $(detect_os) == "debian"; then
        run_command "sudo apt-get autoremove -y"
    fi
    
    log_message "Removing PostgreSQL data..."
    run_command "sudo rm -rf /var/lib/postgresql"
    run_command "sudo rm -rf /etc/postgresql"
}

# Uninstall Node.js and related tools
uninstall_node() {
    log_message "Uninstalling nvm and related tools..."
    run_command "rm -rf \$NVM_DIR"
    run_command "rm -rf ~/.npm"
    run_command "rm -rf ~/.bower"
}

# Uninstall PHP
uninstall_php() {
    log_message "Uninstalling PHP ${PHP_VERSION} and extensions..."
    uninstall_package "php${PHP_VERSION}"
    uninstall_package "php${PHP_VERSION}-common"
    uninstall_package "php${PHP_VERSION}-pgsql"
    uninstall_package "php${PHP_VERSION}-curl"
    uninstall_package "php${PHP_VERSION}-opcache"
    uninstall_package "php${PHP_VERSION}-intl"
    uninstall_package "php${PHP_VERSION}-xml"
    uninstall_package "php${PHP_VERSION}-dom"
    uninstall_package "php${PHP_VERSION}-zip"

    log_message "Uninstalling PHP dependencies..."
    uninstall_package "ca-certificates"
    uninstall_package "apt-transport-https"
    uninstall_package "software-properties-common"
    uninstall_package "lsb-release"
    uninstall_package "openssl"
    uninstall_package "zip"
    uninstall_package "unzip"

    log_message "Uninstalling Composer..."
    run_command "sudo rm -rf /usr/local/bin/composer"
}

# Main uninstallation process
main() {
    log_message "Starting uninstallation process..."
    
    uninstall_postgres
    uninstall_node
    uninstall_php
    
    log_message "Uninstallation completed successfully."
}

# Run the main function
main
