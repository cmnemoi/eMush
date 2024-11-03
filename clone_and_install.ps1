# Set strict mode
Set-StrictMode -Version Latest
$ErrorActionPreference = "Stop"

# Function to check if required commands are installed
function Test-RequiredCommands {
    if (-not (Get-Command git -ErrorAction SilentlyContinue)) {
        Write-Error "Error: git is not installed. Please install git and curl to run this script."
        exit 1
    }

    if (-not (Get-Command curl -ErrorAction SilentlyContinue)) {
        Write-Error "Error: curl is not installed. Please install git and curl to run this script."
        exit 1
    }
}

# Function to clone the repository
function Clone-Repository {
    Write-Host "Cloning eMush repository..."
    git clone https://gitlab.com/eternaltwin/mush/mush.git
    Set-Location mush
}

# Function to launch the install script
function Start-InstallScript {
    Write-Host "Launching install script..."
    .\install.ps1
}

# Main execution block
function Start-Installation {
    try {
        Test-RequiredCommands
        Clone-Repository
        Start-InstallScript
    }
    catch {
        Write-Error "An error occurred: $_"
        exit 1
    }
}

# Run the script
Start-Installation
