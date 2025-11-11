<?php
/**
 * Application Configuration
 */

// Application settings
define('APP_NAME', 'Logistics Management System');
define('APP_VERSION', '1.0.0');
define('APP_URL', 'http://localhost/logistics-app');

// File paths
define('ROOT_PATH', __DIR__ . '/../');
define('UPLOADS_PATH', ROOT_PATH . 'uploads/');
define('TEMPLATES_PATH', ROOT_PATH . 'templates/');
define('ASSETS_PATH', ROOT_PATH . 'assets/');

// Create uploads directory if it doesn't exist
if (!file_exists(UPLOADS_PATH)) {
    mkdir(UPLOADS_PATH, 0755, true);
}

// Error reporting (for development)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Timezone
date_default_timezone_set('Africa/Johannesburg');

// VAT rate (South Africa)
define('VAT_RATE', 15);

// Default payment terms (days)
define('DEFAULT_PAYMENT_TERMS', 30);

// Company branding for PDFs
define('COMPANY_NAME', 'Logistics Management System');
define('COMPANY_ADDRESS', '123 Business Street, Industrial Area');
define('COMPANY_CITY', 'Johannesburg, 2000');
define('COMPANY_PHONE', '+27 (0)11 123 4567');
define('COMPANY_EMAIL', 'info@logisticscompany.co.za');
define('COMPANY_VAT_NUMBER', 'VAT123456789');
define('COMPANY_REGISTRATION', 'Reg: 2023/123456/07');

// Email Configuration (PHPMailer)
// For Gmail testing:
// 1. Use your Gmail address as SMTP_USERNAME
// 2. Generate an "App Password" from your Google Account settings
// 3. Go to: Google Account > Security > 2-Step Verification > App passwords
// 4. Use that app password as SMTP_PASSWORD
define('SMTP_ENABLED', true);
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_SECURE', 'tls'); // 'tls' or 'ssl'
define('SMTP_USERNAME', 'your-email@gmail.com'); // Your Gmail address
define('SMTP_PASSWORD', 'your-app-password'); // Gmail App Password (not your regular password)
define('SMTP_FROM_EMAIL', 'your-email@gmail.com'); // Email address to send from
define('SMTP_FROM_NAME', 'Logistics Management System'); // Name to display as sender
?>
