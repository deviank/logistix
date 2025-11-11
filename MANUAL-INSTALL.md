# Manual PHPMailer Installation Guide

Since Composer is having network issues, here's how to install PHPMailer manually:

## Method 1: Using PowerShell Script (Easiest)

1. Open PowerShell in your project directory:
   ```powershell
   cd C:\wamp64\www\logistics-app
   ```

2. Run the download script:
   ```powershell
   .\download-phpmailer.ps1
   ```

This will automatically download and install PHPMailer for you.

## Method 2: Manual Download

1. **Download PHPMailer:**
   - Go to: https://github.com/PHPMailer/PHPMailer/releases
   - Download the latest release (e.g., `PHPMailer-6.9.1.zip`)

2. **Extract and Install:**
   - Extract the ZIP file
   - Copy the `src` folder from the extracted PHPMailer folder
   - Paste it into your project: `logistics-app/phpmailer/src/`

3. **Verify Installation:**
   Your folder structure should look like this:
   ```
   logistics-app/
     phpmailer/
       src/
         Exception.php
         PHPMailer.php
         SMTP.php
         (and other files)
   ```

## Method 3: Direct Download Link

If you prefer, you can download directly:
- Latest release: https://github.com/PHPMailer/PHPMailer/archive/refs/tags/v6.9.1.zip

After downloading:
1. Extract the ZIP
2. Copy the `src` folder to `phpmailer/src/` in your project

## After Installation

Once PHPMailer is installed:

1. **Configure Email Settings** in `config/config.php`:
   ```php
   define('SMTP_USERNAME', 'your-email@gmail.com');
   define('SMTP_PASSWORD', 'your-app-password');
   define('SMTP_FROM_EMAIL', 'your-email@gmail.com');
   ```

2. **Get Gmail App Password** (see EMAIL-SETUP.md for details)

3. **Test Email** by sending a statement from your application

## Troubleshooting Composer Network Issues

If you want to fix Composer instead:

1. **Check your internet connection**
2. **Try using a different DNS** (e.g., Google DNS: 8.8.8.8)
3. **Check firewall/proxy settings**
4. **Try again later** - packagist.org might be temporarily unavailable

But manual installation is often faster and easier!

