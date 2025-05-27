#!/bin/bash

set -e
set -o pipefail

POSTGRES_VERSION=14
PHP_VERSION=8.3
LOG_FILE="uninstall.log"

# Function to check for sudo permissions
check_sudo() {
    log_message "This script requires sudo permissions to install dependencies. Note: you should probably not run random scripts from the Internet without checking the code first. Do you want to continue? (y/n)."
    read -r response
    if [ "$response" != "y" ]; then
        log_message "Exiting..."
        exit 1
    fi
    log_message "Thank you. Please provide your password when prompted."
}

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
            run_command "sudo pacman -Rf --noconfirm $package_name"
            ;;
        macos)
            if command -v brew &> /dev/null; then
                run_command "brew uninstall $package_name"
            else
                log_message "Homebrew is not installed. Cannot uninstall $package_name."
            fi
            ;;
        *)
            log_message "Unsupported operating system. Currently only Debian-based, Arch-based and macOS are supported."
            ;;
    esac
}


# Uninstall PostgreSQL
uninstall_postgres() {
    log_message "Uninstalling PostgreSQL ${POSTGRES_VERSION}..."
    uninstall_package "postgresql-${POSTGRES_VERSION}"
    uninstall_package "postgresql-client-common"
    uninstall_package "postgresql-client-${POSTGRES_VERSION}"

    
    log_message "Removing PostgreSQL repositories..."
    uninstall_package "postgresql-common"
    run_command "sudo rm -rf /etc/apt/sources.list.d/pgdg.list"
    
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
    uninstall_package "php${PHP_VERSION}-mbstring"

    log_message "Remove PHP repositories..."
    run_command "sudo rm -rf /etc/apt/sources.list.d/php.list"

    log_message "Uninstalling Composer..."
    run_command "sudo rm -rf /usr/local/bin/composer"
}

autoremove() {
    local os_type=$(detect_os)
    case $os_type in
        debian)
            log_message "Running: sudo apt-get autoremove -y"
            run_command "sudo apt-get autoremove -y"
            ;;
        macos)
            log_message "Running: brew cleanup"
            run_command "brew cleanup"
            ;;
        *)
            # Do nothing
            ;;
    esac
}

# Main uninstallation process
main() {
    check_sudo
    
    log_message "Starting uninstallation process..."
    
    uninstall_postgres
    uninstall_node
    uninstall_php
    autoremove
    
    log_message "Uninstallation completed successfully."
}

# Run the main function
main
