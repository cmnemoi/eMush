# PowerShell script for uninstalling the project on Windows

# Define variables
$NODE_VERSION = "22.9.0"
$POSTGRES_VERSION = "16"
$PHP_VERSION = "8.3.12"
$LOG_FILE = "uninstall.log"

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

# Function to uninstall PostgreSQL
function Uninstall-Postgres {
    Log-Message "Stopping PostgreSQL services..."
    Run-Command "net stop postgresql-x64-$POSTGRES_VERSION"
    
    Log-Message "Uninstalling PostgreSQL..."
    Run-Command "choco uninstall postgresql$POSTGRES_VERSION -y"
    
    Log-Message "Removing PostgreSQL data directory..."
    Run-Command "Remove-Item -Path 'C:\Program Files\PostgreSQL' -Recurse -Force -ErrorAction SilentlyContinue"
}

# Function to uninstall Node.js and related tools
function Uninstall-Frontend {
    Log-Message "Uninstalling Node.js..."
    Run-Command "choco uninstall nodejs -y"
    
    Log-Message "Uninstalling Yarn..."
    Run-Command "choco uninstall yarn -y"
    
    Log-Message "Removing Node.js related directories..."
    Run-Command "Remove-Item -Path '$env:APPDATA\npm' -Recurse -Force -ErrorAction SilentlyContinue"
    Run-Command "Remove-Item -Path '$env:APPDATA\npm-cache' -Recurse -Force -ErrorAction SilentlyContinue"
    Run-Command "Remove-Item -Path '$env:LOCALAPPDATA\Yarn' -Recurse -Force -ErrorAction SilentlyContinue"
}

# Function to uninstall PHP and Composer
function Uninstall-Backend {
    Log-Message "Uninstalling PHP..."
    Run-Command "choco uninstall php -y"
    
    Log-Message "Uninstalling Composer..."
    Run-Command "choco uninstall composer -y"
    
    Log-Message "Removing PHP and Composer directories..."
    Run-Command "Remove-Item -Path 'C:\tools\php' -Recurse -Force -ErrorAction SilentlyContinue"
    Run-Command "Remove-Item -Path '$env:APPDATA\Composer' -Recurse -Force -ErrorAction SilentlyContinue"
}

# Function to clean up project files
function Clean-ProjectFiles {
    Log-Message "Removing project files..."
    Run-Command "Remove-Item -Path 'Api' -Recurse -Force -ErrorAction SilentlyContinue"
    Run-Command "Remove-Item -Path 'App' -Recurse -Force -ErrorAction SilentlyContinue"
    Run-Command "Remove-Item -Path 'EternalTwin' -Recurse -Force -ErrorAction SilentlyContinue"
}

# Main uninstallation process
function Main {
    Check-Admin
    Uninstall-Postgres
    Uninstall-Frontend
    Uninstall-Backend
    Clean-ProjectFiles
    
    Log-Message "Uninstallation completed successfully."
}

# Run the main uninstallation process
Main
