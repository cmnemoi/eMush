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

# Uninstall PostgreSQL
uninstall_postgres() {
    log_message "Uninstalling PostgreSQL ${POSTGRES_VERSION}..."
    run_command "sudo apt-get -yq purge postgresql-${POSTGRES_VERSION}"
    
    log_message "Removing PostgreSQL repositories..."
    run_command "sudo apt-get -yq remove postgresql-common"
    run_command "sudo apt-get -yq autoremove"
    
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
    log_message "Uninstalling PHP ${PHP_VERSION}..."
    run_command "sudo apt-get -yq purge php${PHP_VERSION}"
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
