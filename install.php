<?php
/**
 * Logistics App Installation Script
 * Run this once to set up the application
 */

// Start session
session_start();

// Include configuration
require_once 'config/database.php';

$step = $_GET['step'] ?? 1;
$error = '';
$success = '';

// Handle form submissions
if ($_POST) {
    switch ($step) {
        case 1:
            // Test database connection
            try {
                $db = new Database();
                $conn = $db->getConnection();
                if ($conn) {
                    header('Location: install.php?step=2');
                    exit;
                } else {
                    $error = 'Database connection failed. Please check your configuration.';
                }
            } catch (Exception $e) {
                $error = 'Database error: ' . $e->getMessage();
            }
            break;
            
        case 2:
            // Import database schema
            try {
                $db = new Database();
                $sql = file_get_contents('setup.sql');
                
                // Split SQL into individual statements
                $statements = array_filter(array_map('trim', explode(';', $sql)));
                
                foreach ($statements as $statement) {
                    if (!empty($statement)) {
                        $db->query($statement);
                    }
                }
                
                $success = 'Database schema imported successfully!';
                header('Location: install.php?step=3');
                exit;
            } catch (Exception $e) {
                $error = 'Import error: ' . $e->getMessage();
            }
            break;
            
        case 3:
            // Create uploads directory
            $uploadsDir = __DIR__ . '/uploads/';
            if (!is_dir($uploadsDir)) {
                mkdir($uploadsDir, 0755, true);
            }
            
            $success = 'Installation completed successfully!';
            header('Location: index.php');
            exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logistics App - Installation</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .step {
            background: #007cba;
            color: white;
            padding: 10px 20px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 4px;
            margin: 20px 0;
        }
        .success {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 4px;
            margin: 20px 0;
        }
        .btn {
            background: #007cba;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        .btn:hover {
            background: #005a87;
        }
        .form-group {
            margin: 15px 0;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-group input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .progress {
            display: flex;
            justify-content: space-between;
            margin: 20px 0;
        }
        .progress-item {
            flex: 1;
            text-align: center;
            padding: 10px;
            background: #e9ecef;
            margin: 0 5px;
            border-radius: 4px;
        }
        .progress-item.active {
            background: #007cba;
            color: white;
        }
        .progress-item.completed {
            background: #28a745;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚛 Logistics App Installation</h1>
        
        <div class="progress">
            <div class="progress-item <?php echo $step >= 1 ? ($step > 1 ? 'completed' : 'active') : ''; ?>">
                Step 1: Database
            </div>
            <div class="progress-item <?php echo $step >= 2 ? ($step > 2 ? 'completed' : 'active') : ''; ?>">
                Step 2: Schema
            </div>
            <div class="progress-item <?php echo $step >= 3 ? 'active' : ''; ?>">
                Step 3: Complete
            </div>
        </div>
        
        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <?php if ($step == 1): ?>
            <div class="step">Step 1: Database Connection Test</div>
            <p>Let's test your database connection first.</p>
            
            <h3>Current Configuration:</h3>
            <ul>
                <li><strong>Host:</strong> localhost</li>
                <li><strong>Database:</strong> logistics_db</li>
                <li><strong>Username:</strong> root</li>
                <li><strong>Password:</strong> (empty)</li>
            </ul>
            
            <p><strong>Before proceeding, make sure:</strong></p>
            <ul>
                <li>WAMP Server is running (green icon)</li>
                <li>MySQL service is active</li>
                <li>Database 'logistics_db' exists in phpMyAdmin</li>
            </ul>
            
            <form method="post">
                <button type="submit" class="btn">Test Database Connection</button>
            </form>
            
        <?php elseif ($step == 2): ?>
            <div class="step">Step 2: Import Database Schema</div>
            <p>Now let's import the database schema and sample data.</p>
            
            <p>This will create the following tables:</p>
            <ul>
                <li>companies (customer database)</li>
                <li>load_sheets (delivery records)</li>
                <li>invoices (billing records)</li>
                <li>statements (monthly statements)</li>
                <li>statement_items (statement details)</li>
            </ul>
            
            <p>Sample data will include 3 companies, load sheets, and invoices for testing.</p>
            
            <form method="post">
                <button type="submit" class="btn">Import Database Schema</button>
            </form>
            
        <?php elseif ($step == 3): ?>
            <div class="step">Step 3: Final Setup</div>
            <p>Creating uploads directory and finalizing installation...</p>
            
            <form method="post">
                <button type="submit" class="btn">Complete Installation</button>
            </form>
        <?php endif; ?>
        
        <hr>
        <p><small>
            <strong>Need help?</strong> Check the <a href="WAMP-SETUP-GUIDE.md">WAMP Setup Guide</a> or 
            <a href="test-connection.php">run the connection test</a>.
        </small></p>
    </div>
</body>
</html>
