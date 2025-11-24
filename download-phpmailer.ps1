# PowerShell script to download PHPMailer and DomPDF manually
# Run this script: .\download-phpmailer.ps1

Write-Host "PHPMailer & DomPDF Manual Download Script" -ForegroundColor Cyan
Write-Host "==========================================" -ForegroundColor Cyan
Write-Host ""

# Check if phpmailer directory exists
if (Test-Path "phpmailer") {
    Write-Host "Warning: phpmailer directory already exists!" -ForegroundColor Yellow
    $response = Read-Host "Do you want to remove it and re-download? (y/n)"
    if ($response -eq "y" -or $response -eq "Y") {
        Remove-Item -Recurse -Force "phpmailer"
        Write-Host "Removed existing phpmailer directory" -ForegroundColor Green
    } else {
        Write-Host "Cancelled. Exiting." -ForegroundColor Red
        exit
    }
}

Write-Host "Downloading PHPMailer..." -ForegroundColor Yellow

# Create phpmailer directory
New-Item -ItemType Directory -Path "phpmailer" -Force | Out-Null

# Download PHPMailer ZIP from GitHub
$zipUrl = "https://github.com/PHPMailer/PHPMailer/archive/refs/tags/v6.9.1.zip"
$zipFile = "phpmailer.zip"

try {
    Write-Host "Downloading from GitHub..." -ForegroundColor Yellow
    Invoke-WebRequest -Uri $zipUrl -OutFile $zipFile -UseBasicParsing
    
    Write-Host "Extracting files..." -ForegroundColor Yellow
    
    # Extract ZIP file
    Expand-Archive -Path $zipFile -DestinationPath "phpmailer-temp" -Force
    
    # Move src folder to correct location
    Move-Item -Path "phpmailer-temp\PHPMailer-6.9.1\src" -Destination "phpmailer\src" -Force
    
    # Clean up
    Remove-Item -Recurse -Force "phpmailer-temp"
    Remove-Item -Force $zipFile
    
    Write-Host ""
    Write-Host "✓ PHPMailer installed successfully!" -ForegroundColor Green
    Write-Host ""
    Write-Host "Next steps:" -ForegroundColor Cyan
    Write-Host "1. Configure your email settings in config/config.php" -ForegroundColor White
    Write-Host "2. See EMAIL-SETUP.md for detailed instructions" -ForegroundColor White
    Write-Host ""
    
} catch {
    Write-Host ""
    Write-Host "✗ Error downloading PHPMailer: $_" -ForegroundColor Red
    Write-Host ""
    Write-Host "Alternative: Manual download" -ForegroundColor Yellow
    Write-Host "1. Go to: https://github.com/PHPMailer/PHPMailer/releases" -ForegroundColor White
    Write-Host "2. Download the latest release ZIP file" -ForegroundColor White
    Write-Host "3. Extract it and copy the 'src' folder to: phpmailer/src/" -ForegroundColor White
    Write-Host ""
}

