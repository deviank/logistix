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
        $app->handleAjax();
        break;
    default:
        $app->showDashboard();
        break;
}
?>
