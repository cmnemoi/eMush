#!/bin/bash

# Function to log messages
log_message() {
    echo "$1"
}

# Stop PHP server
stop_php_server() {
    log_message "Stopping PHP server..."
    pkill -f "php -S localhost:8080"
    if [ $? -eq 0 ]; then
        log_message "PHP server stopped successfully."
    else
        log_message "No PHP server was running or failed to stop."
    fi
}

# Stop Vite server
stop_vite_server() {
    log_message "Stopping Vite server..."
    pkill -f "vite"
    if [ $? -eq 0 ]; then
        log_message "Vite server stopped successfully."
    else
        log_message "No Vite server was running or failed to stop."
    fi
}

# Main function
main() {
    stop_php_server
    stop_vite_server
    log_message "All servers have been stopped."
}

# Run the main function
main
