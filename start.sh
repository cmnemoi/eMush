#!/bin/bash

# Constants
PID_FILE="server_pids"

# Function to log messages
log_message() {
    echo "$1"
}

# Function to write PID to file
write_pid() {
    local service_name=$1
    local pid=$2
    echo "${service_name}:${pid}" >> $PID_FILE
}

# Clean previous PID file if exists
if [ -f "$PID_FILE" ]; then
    rm $PID_FILE
fi

# Start PHP server
start_php_server() {
    log_message "Starting PHP server..."
    php -S localhost:8080 -t Api/public > /dev/null 2>&1 &
    PHP_PID=$!
    if [ $? -eq 0 ]; then
        write_pid "php" $PHP_PID
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
        write_pid "vite" $VITE_PID
        log_message "Vite server started successfully (PID: $VITE_PID)."
    else
        log_message "Failed to start Vite server."
        exit 1
    fi
}

start_eternaltwin_server() {
    log_message "Starting Eternaltwin server..."
    cd Eternaltwin && yarn etwin start > /dev/null 2>&1 &
    ETWIN_PID=$!
    if [ $? -eq 0 ]; then
        write_pid "eternaltwin" $ETWIN_PID
        log_message "Eternaltwin server started successfully (PID: $ETWIN_PID)."
    else
        log_message "Failed to start Eternaltwin server."
        exit 1
    fi
}

# Main function
main() {
    start_php_server
    start_vite_server
    start_eternaltwin_server
    log_message "Project started successfully."
    log_message "You can access the project at http://localhost:5173"
    log_message "To stop the servers, run make stop."
}

# Run the main function
main