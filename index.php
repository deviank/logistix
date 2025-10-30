<?php
/**
 * Logistics Application - Main Entry Point
 * Lightweight custom PHP application for logistics management
 */

// Start session
session_start();

// Configuration
require_once 'config/database.php';
require_once 'config/config.php';

// Autoload classes
spl_autoload_register(function ($class) {
    $file = 'classes/' . $class . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// Initialize database connection
$db = new Database();

// Get current page
$page = $_GET['page'] ?? 'dashboard';

// Initialize the application
$app = new LogisticsApp($db);

// Route the request
switch ($page) {
    case 'dashboard':
        $app->showDashboard();
        break;
    case 'companies':
        $app->showCompanies();
        break;
    case 'loadsheets':
        $app->showLoadSheets();
        break;
    case 'invoices':
        $app->showInvoices();
        break;
    case 'statements':
        $app->showStatements();
        break;
    case 'ajax':
        // Ensure clean JSON-only output for AJAX
        if (function_exists('ob_get_level')) {
            while (ob_get_level() > 0) { ob_end_clean(); }
        }
        header('Content-Type: application/json');
        $app->handleAjax();
        exit; // prevent any further output
    default:
        $app->showDashboard();
        break;
}
?>
