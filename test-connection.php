<?php
/**
 * Database Connection Test
 * Use this file to test if your database connection is working
 */

// Include configuration
require_once 'config/database.php';

echo "<h1>Logistics App - Database Connection Test</h1>";

try {
    // Test database connection
    $db = new Database();
    $conn = $db->getConnection();
    
    if ($conn) {
        echo "<p style='color: green;'>✅ Database connection successful!</p>";
        
        // Test if tables exist
        $tables = ['companies', 'load_sheets', 'invoices', 'statements', 'statement_items'];
        
        echo "<h2>Checking Tables:</h2>";
        foreach ($tables as $table) {
            $result = $db->fetchOne("SHOW TABLES LIKE ?", [$table]);
            if ($result) {
                echo "<p style='color: green;'>✅ Table '$table' exists</p>";
            } else {
                echo "<p style='color: red;'>❌ Table '$table' missing</p>";
            }
        }
        
        // Test sample data
        echo "<h2>Checking Sample Data:</h2>";
        
        $companies = $db->fetchAll("SELECT COUNT(*) as count FROM companies");
        echo "<p>Companies: " . $companies[0]['count'] . "</p>";
        
        $loadSheets = $db->fetchAll("SELECT COUNT(*) as count FROM load_sheets");
        echo "<p>Load Sheets: " . $loadSheets[0]['count'] . "</p>";
        
        $invoices = $db->fetchAll("SELECT COUNT(*) as count FROM invoices");
        echo "<p>Invoices: " . $invoices[0]['count'] . "</p>";
        
        if ($companies[0]['count'] > 0 && $loadSheets[0]['count'] > 0 && $invoices[0]['count'] > 0) {
            echo "<p style='color: green;'>✅ Sample data loaded successfully!</p>";
        } else {
            echo "<p style='color: orange;'>⚠️ Sample data may be missing. Please import setup.sql</p>";
        }
        
        // Test file permissions
        echo "<h2>Checking File Permissions:</h2>";
        
        $uploadsDir = __DIR__ . '/uploads/';
        if (is_dir($uploadsDir)) {
            if (is_writable($uploadsDir)) {
                echo "<p style='color: green;'>✅ Uploads directory is writable</p>";
            } else {
                echo "<p style='color: red;'>❌ Uploads directory is not writable</p>";
            }
        } else {
            echo "<p style='color: red;'>❌ Uploads directory does not exist</p>";
        }
        
        echo "<h2>Configuration Check:</h2>";
        echo "<p>App URL: " . (defined('APP_URL') ? APP_URL : 'Not defined') . "</p>";
        echo "<p>Uploads Path: " . (defined('UPLOADS_PATH') ? UPLOADS_PATH : 'Not defined') . "</p>";
        
        echo "<hr>";
        echo "<p><strong>If all checks pass, your application should be working!</strong></p>";
        echo "<p><a href='index.php'>Go to Logistics Dashboard</a></p>";
        
    } else {
        echo "<p style='color: red;'>❌ Database connection failed!</p>";
        echo "<p>Please check your database configuration in config/database.php</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
    echo "<p>Please check your database configuration and ensure MySQL is running.</p>";
}

echo "<hr>";
echo "<h2>WAMP Status Check:</h2>";
echo "<p>PHP Version: " . phpversion() . "</p>";
echo "<p>MySQL Extension: " . (extension_loaded('mysql') || extension_loaded('mysqli') ? '✅ Loaded' : '❌ Not loaded') . "</p>";
echo "<p>PDO Extension: " . (extension_loaded('pdo') ? '✅ Loaded' : '❌ Not loaded') . "</p>";
echo "<p>PDO MySQL: " . (extension_loaded('pdo_mysql') ? '✅ Loaded' : '❌ Not loaded') . "</p>";
?>
