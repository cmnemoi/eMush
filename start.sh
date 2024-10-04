#!/bin/bash

# Function to log messages
log_message() {
    echo "$1"
}

# Start PHP server
start_php_server() {
    log_message "Starting PHP server..."
    php -S localhost:8080 -t Api/public > /dev/null 2>&1 &
    PHP_PID=$!
    if [ $? -eq 0 ]; then
        log_message "PHP server started successfully (PID: $PHP_PID)."
    else
        log_message "Failed to start PHP server."
        exit 1
    fi
}

# Start Vite server
start_vite_server() {
    log_message "Starting Vite server..."
    cd App && yarn dev > /dev/null 2>&1 &
    VITE_PID=$!
    if [ $? -eq 0 ]; then
        log_message "Vite server started successfully (PID: $VITE_PID)."
    else
        log_message "Failed to start Vite server."
        exit 1
    fi
}

# Main function
main() {
    start_php_server
    start_vite_server
    log_message "Project started successfully."
    log_message "You can access the project at http://localhost:5173"
    log_message "To stop the servers, run ./stop.sh"
}

# Run the main function
main
