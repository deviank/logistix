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
?>
