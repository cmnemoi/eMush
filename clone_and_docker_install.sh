#!/bin/bash

set -e
set -o pipefail

# Function to check if required commands are installed
check_required_commands() {
    if ! command -v git >/dev/null 2>&1; then
        echo "Error: git is not installed. Please install git and curl to run this script."
        exit 1
    fi

    if ! command -v curl >/dev/null 2>&1; then
        echo "Error: curl is not installed. Please install git and curl to run this script."
        exit 1
    fi
}

# Function to clone the repository
clone_repository() {
    echo "Cloning eMush repository..."
    git clone https://gitlab.com/eternaltwin/mush/mush.git && cd mush
}

# Function to launch the install script
launch_install_script() {
    echo "Launching install script..."
    source ./docker_install.sh
}

run() {
    check_required_commands
    clone_repository
    launch_install_script
}

run