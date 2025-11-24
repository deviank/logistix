<?php
/**
 * Database Setup Script
 * Run this file in your browser after starting MySQL in XAMPP
 * URL: http://localhost/logistix/setup-database.php
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Logistics Database Setup</h1>";
echo "<p>Setting up database...</p>";

try {
    // Connect to MySQL (without database first)
    $pdo = new PDO('mysql:host=localhost', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<p>✓ Connected to MySQL</p>";
    
    // Read and execute SQL file
    $sqlFile = __DIR__ . '/setup.sql';
    
    if (!file_exists($sqlFile)) {
        throw new Exception("SQL file not found: $sqlFile");
    }
    
    $sql = file_get_contents($sqlFile);
    
    // Split by semicolons and execute each statement
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    $executed = 0;
    foreach ($statements as $statement) {
        if (!empty($statement) && !preg_match('/^--/', $statement)) {
            try {
                $pdo->exec($statement);
                $executed++;
            } catch (PDOException $e) {
                // Ignore "database exists" and "table exists" errors
                if (strpos($e->getMessage(), 'already exists') === false) {
                    echo "<p style='color: orange;'>⚠ " . htmlspecialchars($e->getMessage()) . "</p>";
                }
            }
        }
    }
    
    echo "<p>✓ Executed $executed SQL statements</p>";
    
    // Verify database was created
    $result = $pdo->query("SHOW DATABASES LIKE 'logistics_db'");
    if ($result->rowCount() > 0) {
        echo "<p>✓ Database 'logistics_db' exists</p>";
        
        // Check tables
        $pdo->exec("USE logistics_db");
        $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        
        echo "<p>✓ Found " . count($tables) . " tables:</p>";
        echo "<ul>";
        foreach ($tables as $table) {
            echo "<li>$table</li>";
        }
        echo "</ul>";
        
        // Check for sample data
        $companies = $pdo->query("SELECT COUNT(*) FROM companies")->fetchColumn();
        echo "<p>✓ Companies in database: $companies</p>";
        
        if ($companies > 0) {
            echo "<h2 style='color: green;'>✓ Setup Complete!</h2>";
            echo "<p><a href='index.php'>Go to Application</a></p>";
        } else {
            echo "<p style='color: orange;'>⚠ Database created but no sample data found. You may need to import setup.sql manually via phpMyAdmin.</p>";
        }
    } else {
        throw new Exception("Database was not created");
    }
    
} catch (PDOException $e) {
    echo "<h2 style='color: red;'>✗ Database Connection Error</h2>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>Please make sure:</strong></p>";
    echo "<ul>";
    echo "<li>XAMPP Control Panel is open</li>";
    echo "<li>MySQL service is started (green in XAMPP Control Panel)</li>";
    echo "<li>MySQL is running on port 3306</li>";
    echo "</ul>";
} catch (Exception $e) {
    echo "<h2 style='color: red;'>✗ Error</h2>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
}
?>

