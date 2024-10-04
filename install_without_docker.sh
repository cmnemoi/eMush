#!/bin/bash

# Function to check if a command exists
command_exists() {
    command -v "$1" >/dev/null 2>&1
}

# Install dependencies
install_dependencies() {
    if command_exists apt-get; then
        sudo apt-get update
        sudo apt-get install -y php postgresql nodejs npm
    elif command_exists yum; then
        sudo yum update
        sudo yum install -y php postgresql nodejs npm
    else
        echo "Unsupported package manager. Please install PHP, PostgreSQL, Node.js, and npm manually."
        exit 1
    fi

    # Install Yarn
    sudo npm install -g yarn
}

# Install PHP development tools
install_php_tools() {
    composer global require friendsofphp/php-cs-fixer
    composer global require vimeo/psalm
    composer global require phpmd/phpmd
}

# Install Node.js development tools
install_node_tools() {
    npm install -g eslint
}

# Setup environment variables
setup_env_variables() {
    cp ./Api/.env.dist ./Api/.env
    cp ./App/.env.dist ./App/.env
    cp ./Eternaltwin/eternaltwin.toml ./Eternaltwin/eternaltwin.local.toml
}

# Setup Git hooks
setup_git_hooks() {
    chmod +x hooks/pre-commit
    chmod +x hooks/pre-push
    git config core.hooksPath hooks
}

# Setup PHPMD configuration
setup_phpmd() {
    if [ ! -f Api/phpmd.xml ]; then
        echo "Error: Api/phpmd.xml file not found. Please ensure it exists in the project directory."
        exit 1
    fi
    echo "PHPMD configuration file (phpmd.xml) is in place."
}

# Install API dependencies
install_api() {
    cd Api
    composer install
    ./reset.sh --init
    cd ..
}

# Install Front-end dependencies
install_front() {
    cd App
    yarn install
    ./reset.sh
    cd ..
}

# Install Eternaltwin dependencies
install_eternaltwin() {
    cd Eternaltwin
    yarn install
    yarn etwin db reset
    yarn etwin db sync
    cd ..
}

# Setup JWT certificates
setup_jwt_certificates() {
    cd Api
    openssl genpkey -pass pass:mush -out config/jwt/private.pem -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096
    openssl pkey -passin pass:mush -in config/jwt/private.pem -out config/jwt/public.pem -pubout
    chmod go+r config/jwt/private.pem
    cd ..
}

# Fill Daedalus (assuming this is a PHP command)
fill_daedalus() {
    cd Api
    php bin/console mush:fill-daedalus
    cd ..
}

# Main installation process
main() {
    install_dependencies
    install_php_tools
    install_node_tools
    setup_env_variables
    setup_git_hooks
    setup_phpmd
    install_api
    install_front
    install_eternaltwin
    setup_jwt_certificates
    fill_daedalus

    echo "Installation completed successfully! You can access eMush at http://localhost/."
    echo "You can log in with the following credentials:"
    echo "Username: chun"
    echo "Password: 1234567891"
    echo "Note: Make sure to set up and start your web server and database services."
}

# Run the main installation process
main
