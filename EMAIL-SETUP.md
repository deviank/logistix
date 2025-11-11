# Email Setup Guide - PHPMailer Configuration

This guide will help you set up email functionality using PHPMailer with Gmail for testing.

## Step 1: Install PHPMailer

You have three options:

### Option A: Using PowerShell Script (Easiest - Recommended)

1. Open PowerShell in your project directory:
   ```powershell
   cd C:\wamp64\www\logistics-app
   ```

2. Run the download script:
   ```powershell
   .\download-phpmailer.ps1
   ```

This will automatically download and install PHPMailer for you!

### Option B: Using Composer

1. Open Command Prompt or PowerShell
2. Navigate to your project directory:
   ```bash
   cd C:\wamp64\www\logistics-app
   ```
3. Install Composer if you don't have it: https://getcomposer.org/download/
4. Run Composer install:
   ```bash
   composer install
   ```

**Note:** If you get network timeout errors with Composer, use Option A or C instead.

### Option C: Manual Installation

1. Download PHPMailer from: https://github.com/PHPMailer/PHPMailer/releases
   - Or use direct link: https://github.com/PHPMailer/PHPMailer/archive/refs/tags/v6.9.1.zip
2. Extract the ZIP file
3. Copy the `src` folder from PHPMailer to your project:
   ```
   logistics-app/
     phpmailer/
       src/
         Exception.php
         PHPMailer.php
         SMTP.php
   ```

See `MANUAL-INSTALL.md` for more detailed manual installation instructions.

## Step 2: Configure Gmail for Testing

To use Gmail SMTP, you need to create an "App Password":

1. **Enable 2-Step Verification** (if not already enabled):
   - Go to your Google Account: https://myaccount.google.com/
   - Click on "Security"
   - Under "Signing in to Google", enable "2-Step Verification"

2. **Generate App Password**:
   - Go to: https://myaccount.google.com/apppasswords
   - Select "Mail" and "Other (Custom name)"
   - Enter "Logistics App" as the name
   - Click "Generate"
   - Copy the 16-character password (you'll use this in config.php)

## Step 3: Update Configuration

Open `config/config.php` and update these settings:

```php
// Email Configuration (PHPMailer)
define('SMTP_ENABLED', true);
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_SECURE', 'tls');
define('SMTP_USERNAME', 'your-email@gmail.com'); // Your Gmail address
define('SMTP_PASSWORD', 'your-16-char-app-password'); // The App Password from Step 2
define('SMTP_FROM_EMAIL', 'your-email@gmail.com'); // Same as SMTP_USERNAME
define('SMTP_FROM_NAME', 'Logistics Management System');
```

**Important:** 
- Replace `your-email@gmail.com` with your actual Gmail address
- Replace `your-16-char-app-password` with the App Password you generated
- Never use your regular Gmail password - always use an App Password

## Step 4: Test Email

1. Go to your Statements page
2. Click "Send Email" on any statement
3. Enter a test email address
4. Click "Send Statement"

If everything is configured correctly, you should see a success message and receive the email!

## Troubleshooting

### Error: "SMTP is not enabled"
- Make sure `SMTP_ENABLED` is set to `true` in `config/config.php`

### Error: "Failed to authenticate"
- Double-check your Gmail address and App Password
- Make sure you're using an App Password, not your regular password
- Ensure 2-Step Verification is enabled on your Google Account

### Error: "Class 'PHPMailer\PHPMailer\PHPMailer' not found"
- PHPMailer is not installed. Follow Step 1 above to install it.

### Error: "Connection timeout"
- Check your internet connection
- Make sure port 587 is not blocked by your firewall
- Try using port 465 with `SMTP_SECURE` set to `'ssl'` instead

## Alternative: Using Other Email Providers

### Outlook/Hotmail
```php
define('SMTP_HOST', 'smtp-mail.outlook.com');
define('SMTP_PORT', 587);
define('SMTP_SECURE', 'tls');
```

### Yahoo Mail
```php
define('SMTP_HOST', 'smtp.mail.yahoo.com');
define('SMTP_PORT', 587);
define('SMTP_SECURE', 'tls');
```

### Custom SMTP Server
```php
define('SMTP_HOST', 'smtp.yourdomain.com');
define('SMTP_PORT', 587); // or 465 for SSL
define('SMTP_SECURE', 'tls'); // or 'ssl' for port 465
```

## Security Notes

- Never commit your `config/config.php` file with real passwords to version control
- Use App Passwords instead of your main account password
- For production, consider using a dedicated email service like SendGrid, Mailgun, or AWS SES

