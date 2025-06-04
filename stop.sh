#!/bin/bash

# Function to log messages
log_message() {
    echo "$1"
}

# Function to read PID from server_pids
get_pid() {
    local server=$1
    local pid=$(grep "^$server:" ./server_pids | cut -d':' -f2)
    echo $pid
}

# Stop a server by PID
stop_server() {
    local server=$1
    local pid=$(get_pid $server)
    
    if [ ! -z "$pid" ]; then
        log_message "Stopping $server server (PID: $pid)..."
        if kill -9 $pid 2>/dev/null; then
            log_message "$server server stopped successfully."
        else
            log_message "$server server was not running (PID $pid not found)."
        fi
    else
        log_message "No PID found for $server server."
    fi
}

# Main function
main() {
    # Check if PID file exists
    if [ ! -f "./server_pids" ]; then
        log_message "Error: server_pids not found!"
        exit 1
    fi

    # Stop each server
    stop_server "php"
    stop_server "vite"
    stop_server "eternaltwin"
    
    log_message "Project stopped successfully."
    log_message "To start the servers again, run make start."
}

# Run the main function
main
