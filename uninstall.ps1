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

# Function to check for admin permissions and confirm
function Check-Admin {
    $currentPrincipal = New-Object Security.Principal.WindowsPrincipal([Security.Principal.WindowsIdentity]::GetCurrent())
    $isAdmin = $currentPrincipal.IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)
    if (-not $isAdmin) {
        Log-Message "This script requires administrator permissions. Please run PowerShell as an administrator."
        exit 1
    }

    Log-Message "This script requires administrator permissions to uninstall components. Note: you should probably not run random scripts from the Internet without checking the code first. Do you want to continue? (y/n)"
    $response = Read-Host
    if ($response -ne "y") {
        Log-Message "Exiting..."
        exit 1
    }
    Log-Message "Thank you. Proceeding with uninstallation..."
}

# Function to detect OS and verify Windows
function Check-SystemCompatibility {
    $os = Get-WmiObject -Class Win32_OperatingSystem
    if (-not $os.Caption.Contains("Windows")) {
        Log-Message "This script is only compatible with Windows operating systems."
        exit 1
    }
    Log-Message "Running on: $($os.Caption)"
}

# Function to uninstall PostgreSQL
function Uninstall-Postgres {
    Log-Message "Uninstalling PostgreSQL ${POSTGRES_VERSION}..."
    
    # Stop services first
    Run-Command "net stop postgresql-x64-$POSTGRES_VERSION" -ErrorAction SilentlyContinue
    
    # Uninstall PostgreSQL
    Run-Command "choco uninstall postgresql$POSTGRES_VERSION -y"
    
    Log-Message "Removing PostgreSQL data..."
    Run-Command "Remove-Item -Path 'C:\Program Files\PostgreSQL' -Recurse -Force -ErrorAction SilentlyContinue"
    Run-Command "Remove-Item -Path 'C:\ProgramData\PostgreSQL' -Recurse -Force -ErrorAction SilentlyContinue"
}

# Function to uninstall Node.js and related tools
function Uninstall-Node {
    Log-Message "Uninstalling nvm and related tools..."
    
    # Remove Node.js
    Run-Command "choco uninstall nodejs -y"
    Run-Command "choco uninstall yarn -y"
    
    # Remove related directories
    Run-Command "Remove-Item -Path '$env:USERPROFILE\.nvm' -Recurse -Force -ErrorAction SilentlyContinue"
    Run-Command "Remove-Item -Path '$env:APPDATA\npm' -Recurse -Force -ErrorAction SilentlyContinue"
    Run-Command "Remove-Item -Path '$env:APPDATA\npm-cache' -Recurse -Force -ErrorAction SilentlyContinue"
}

# Function to uninstall PHP
function Uninstall-PHP {
    Log-Message "Uninstalling PHP ${PHP_VERSION} and extensions..."
    
    # Uninstall PHP and extensions
    $extensions = @(
        "php",
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
        Run-Command "choco uninstall $ext -y"
    }
    
    Log-Message "Uninstalling Composer..."
    Run-Command "choco uninstall composer -y"
    
    # Remove PHP related directories
    Run-Command "Remove-Item -Path 'C:\tools\php' -Recurse -Force -ErrorAction SilentlyContinue"
    Run-Command "Remove-Item -Path '$env:APPDATA\Composer' -Recurse -Force -ErrorAction SilentlyContinue"
}

# Function to clean up remaining packages
function Remove-UnusedPackages {
    Log-Message "Cleaning up unused packages..."
    Run-Command "choco upgrade all -y"  # Updates package list
    Run-Command "choco source remove -n=chocolatey"  # Removes outdated sources
    Run-Command "choco cleanup"  # Cleans up old versions
}

# Main uninstallation process
function Main {
    try {
        Check-SystemCompatibility
        Check-Admin
        
        Log-Message "Starting uninstallation process..."
        
        Uninstall-Postgres
        Uninstall-Node
        Uninstall-PHP
        Remove-UnusedPackages
        
        Log-Message "Uninstallation completed successfully."
    }
    catch {
        Log-Message "Uninstallation failed: $_"
        exit 1
    }
}

# Run the main function
Main