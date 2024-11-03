# PowerShell script for installing the project on Windows using Chocolatey

# Define variables
$NODE_VERSION = "22.9.0"
$POSTGRES_VERSION = "16"
$PHP_VERSION = "8.3.12"
$COMPOSER_VERSION = "6.3.0"
$LOG_FILE = "install.log"

# Function to log messages
function Log-Message {
    param([string]$message)
    Write-Host $message
    Add-Content -Path $LOG_FILE -Value "$(Get-Date -Format 'yyyy-MM-dd HH:mm:ss'): $message"
}

# Function to run commands with logging
function Run-Command {
    param([string]$command)
    Log-Message "Running: $command"
    try {
        Invoke-Expression $command *>&1 | Tee-Object -Append -FilePath $LOG_FILE
        if ($LASTEXITCODE -ne 0) {
            throw "Command failed with exit code $LASTEXITCODE"
        }
    }
    catch {
        Log-Message "Error executing command: $_"
        throw
    }
}

# Function to check Windows version and compatibility
function Check-SystemCompatibility {
    $os = Get-WmiObject -Class Win32_OperatingSystem
    if (-not $os.Caption.Contains("Windows")) {
        Log-Message "This script is only compatible with Windows operating systems."
        exit 1
    }
    Log-Message "Running on: $($os.Caption)"
}

# Function to check for admin permissions
function Check-Admin {
    $currentPrincipal = New-Object Security.Principal.WindowsPrincipal([Security.Principal.WindowsIdentity]::GetCurrent())
    $isAdmin = $currentPrincipal.IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)
    if (-not $isAdmin) {
        Log-Message "This script requires administrator permissions. Please run PowerShell as an administrator."
        exit 1
    }
}

# Function to update system
function Update-System {
    Log-Message "Updating system packages..."
    Run-Command "choco upgrade all -y"
}

# Function to install Chocolatey
function Install-Chocolatey {
    if (-not (Get-Command choco -ErrorAction SilentlyContinue)) {
        Log-Message "Installing Chocolatey..."
        Set-ExecutionPolicy Bypass -Scope Process -Force
        [System.Net.ServicePointManager]::SecurityProtocol = [System.Net.ServicePointManager]::SecurityProtocol -bor 3072
        Invoke-Expression ((New-Object System.Net.WebClient).DownloadString('https://chocolatey.org/install.ps1'))
    } else {
        Log-Message "Chocolatey is already installed"
    }
}

# Function to install and setup PostgreSQL
function Install-Postgres {
    Log-Message "Installing PostgreSQL $POSTGRES_VERSION..."
    Run-Command "choco install postgresql$POSTGRES_VERSION --params '/Password:password' -y"

    Log-Message "Starting PostgreSQL..."
    Run-Command "net start postgresql-x64-$POSTGRES_VERSION"

    Log-Message "Creating users and databases..."
    $env:PGPASSWORD = "password"
    
    # Create users and databases matching install.sh
    $queries = @"
CREATE USER "mysql" WITH PASSWORD 'password';
CREATE DATABASE "mush" WITH OWNER "mysql";
GRANT ALL PRIVILEGES ON DATABASE "mush" TO "mysql";

CREATE USER "etwin.dev" WITH PASSWORD 'password';
CREATE DATABASE "etwin.dev" WITH OWNER "etwin.dev";
GRANT ALL PRIVILEGES ON DATABASE "etwin.dev" TO "etwin.dev";

\c etwin.dev
ALTER SCHEMA public OWNER TO "etwin.dev";
GRANT ALL ON SCHEMA public TO "etwin.dev";
"@
    
    $queries -split "`n" | ForEach-Object {
        if ($_.Trim()) {
            Run-Command "psql -U postgres -c `"$_`""
        }
    }
}

# Function to install front-end dependencies
function Install-Frontend {
    Log-Message "Installing Node.js $NODE_VERSION..."
    Run-Command "choco install nodejs --version=$NODE_VERSION -y"

    Log-Message "Installing Yarn..."
    Run-Command "choco install yarn -y"

    Log-Message "Setup front-end env variables..."
    Run-Command "Copy-Item -Path App\.env.bare-metal -Destination App\.env"

    Log-Message "Installing front-end dependencies..."
    Set-Location App
    Run-Command "yarn install"
    Set-Location ..
}

# Function to install Eternaltwin server
function Install-Eternaltwin {
    Log-Message "Setup Eternaltwin env variables..."
    Run-Command "Copy-Item -Path Eternaltwin\eternaltwin.bare-metal.toml -Destination Eternaltwin\eternaltwin.local.toml"

    Log-Message "Installing Eternaltwin server dependencies..."
    Set-Location Eternaltwin
    Run-Command "yarn set version latest"
    Run-Command "yarn install"
    
    Log-Message "Installing Eternaltwin server..."
    Run-Command "yarn etwin db reset"
    Run-Command "yarn etwin db sync"
    Set-Location ..
}

# Function to install back-end dependencies
function Install-Backend {
    Log-Message "Installing PHP $PHP_VERSION and extensions..."
    Run-Command "choco install php --version=$PHP_VERSION -y"
    
    # Install PHP extensions
    $extensions = @(
        "php-pgsql",
        "php-curl",
        "php-opcache",
        "php-intl",
        "php-xml",
        "php-dom",
        "php-zip",
        "php-mbstring"
    )
    
    foreach ($ext in $extensions) {
        Run-Command "choco install $ext -y"
    }

    Log-Message "Installing Composer..."
    Run-Command "choco install composer -y"

    Log-Message "Creating JWT certificates..."
    Set-Location Api
    Run-Command "openssl genpkey -pass pass:mush -out config/jwt/private.pem -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096"
    Run-Command "openssl pkey -passin pass:mush -in config/jwt/private.pem -out config/jwt/public.pem -pubout"
    Run-Command "icacls config\jwt\private.pem /grant Everyone:R"

    Log-Message "Setup back-end env variables..."
    Run-Command "Copy-Item -Path .env.bare-metal -Destination .env.local"
    Run-Command "Copy-Item -Path .env.bare-metal.test -Destination .env.test.local"

    Log-Message "Installing back-end dependencies..."
    Run-Command "composer install"
    Run-Command "composer reset"
    Set-Location ..
}

# Function to launch the project
function Launch-Project {
    Log-Message "Project installed successfully! You can access it by running make start."
    Log-Message "Use the following credentials to login:"
    Log-Message "Username: chun"
    Log-Message "Password: 1234567891"
}

# Main installation process
function Main {
    try {
        Check-Admin
        Check-SystemCompatibility
        Install-Chocolatey
        Update-System
        Install-Postgres
        Install-Frontend
        Install-Eternaltwin
        Install-Backend
        Launch-Project
    }
    catch {
        Log-Message "Installation failed: $_"
        exit 1
    }
}

# Run the main installation process
Main
