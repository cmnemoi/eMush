# PowerShell script for installing the project on Windows using Chocolatey

# Define variables
$NODE_VERSION = "22.9.0"
$POSTGRES_VERSION = "15"
$PHP_VERSION = "8.3"
$LOG_FILE = "install.log"

# Function to log messages
function Log-Message {
    param([string]$message)
    Write-Host $message
    Add-Content -Path $LOG_FILE -Value $message
}

# Function to run commands with logging
function Run-Command {
    param([string]$command)
    Log-Message "Running: $command"
    Invoke-Expression $command *>&1 | Tee-Object -Append -FilePath $LOG_FILE
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

# Function to install Chocolatey
function Install-Chocolatey {
    Log-Message "Installing Chocolatey..."
    Set-ExecutionPolicy Bypass -Scope Process -Force
    [System.Net.ServicePointManager]::SecurityProtocol = [System.Net.ServicePointManager]::SecurityProtocol -bor 3072
    Invoke-Expression ((New-Object System.Net.WebClient).DownloadString('https://chocolatey.org/install.ps1'))
}

# Function to install and setup PostgreSQL
function Install-Postgres {
    Log-Message "Installing PostgreSQL $POSTGRES_VERSION..."
    Run-Command "choco install postgresql$POSTGRES_VERSION --params '/Password:password' -y"

    Log-Message "Starting PostgreSQL..."
    Run-Command "net start postgresql-x64-$POSTGRES_VERSION"

    Log-Message "Creating users and databases..."
    $env:PGPASSWORD = "password"
    Run-Command "psql -U postgres -c `"CREATE USER `"mysql`" WITH PASSWORD 'password' CREATEDB LOGIN;`""
    Run-Command "psql -U postgres -c `"CREATE DATABASE `"etwin.dev`" WITH OWNER `"mysql`";`""
    Run-Command "psql -U postgres -c `"GRANT ALL PRIVILEGES ON DATABASE `"etwin.dev`" TO `"mysql`";`""
    Run-Command "psql -U postgres -c `"ALTER SCHEMA public OWNER TO `"mysql`";`""
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
    Run-Command "Copy-Item -Path EternalTwin\etwin.bare-metal.toml.example -Destination EternalTwin\etwin.toml"

    Log-Message "Installing Eternaltwin server dependencies..."
    Set-Location EternalTwin
    Run-Command "yarn set version latest"
    Run-Command "yarn install"
    Set-Location ..
}

# Function to install back-end dependencies
function Install-Backend {
    Log-Message "Installing PHP $PHP_VERSION..."
    Run-Command "choco install php --version=$PHP_VERSION -y"

    Log-Message "Installing Composer..."
    Run-Command "choco install composer -y"

    Log-Message "Creating JWT certificates..."
    Set-Location Api
    Run-Command "openssl genpkey -pass pass:mush -out config/jwt/private.pem -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096"
    Run-Command "openssl pkey -passin pass:mush -in config/jwt/private.pem -out config/jwt/public.pem -pubout"
    Run-Command "icacls config\jwt\private.pem /grant Everyone:R"
    Set-Location ..

    Log-Message "Setup back-end env variables..."
    Run-Command "Copy-Item -Path Api\.env.bare-metal -Destination Api\.env.local"
    Run-Command "Copy-Item -Path Api\.env.bare-metal.test -Destination Api\.env.test.local"

    Log-Message "Installing back-end dependencies..."
    Set-Location Api
    Run-Command "composer install"
    Run-Command "composer reset"
    Set-Location ..
}

# Function to launch the project
function Launch-Project {
    Log-Message "Starting back-end server..."
    Start-Process powershell -ArgumentList "-Command php -S localhost:8080 -t Api/public"

    Log-Message "Starting front-end server..."
    Set-Location App
    Start-Process powershell -ArgumentList "-Command yarn dev"
    Set-Location ..

    Log-Message "Starting Eternaltwin server..."
    Set-Location EternalTwin
    Run-Command "yarn etwin db create"
    Start-Process powershell -ArgumentList "-Command yarn etwin start"
    Start-Sleep -Seconds 10
    Set-Location ..

    Log-Message "Create Eternaltwin accounts..."
    Run-Command "php Api\bin\console mush:create-crew"

    Log-Message "Filling a Daedalus with players..."
    Run-Command "php Api\bin\console mush:fill-daedalus"

    Log-Message "Project installed successfully! You can access it at http://localhost:5173"
    Log-Message "Use the following credentials to login:"
    Log-Message "Username: chun"
    Log-Message "Password: 1234567891"
}

# Main installation process
function Main {
    Check-Admin
    Install-Chocolatey
    Install-Postgres
    Install-Frontend
    Install-Eternaltwin
    Install-Backend
    Launch-Project
}

# Run the main installation process
Main
