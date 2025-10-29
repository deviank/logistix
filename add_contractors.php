<?php
/**
 * Add Sample Contractors to Database
 * Run this file once to add sample contractors: http://localhost/logistics-app/add_contractors.php
 */

// Start session
session_start();

// Include configuration
require_once 'config/database.php';

// Initialize database connection
try {
    $db = new Database();
    
    // Sample contractors data
    $contractors = [
        ['name' => 'Around The Clock Transport', 'contact_person' => 'Mike Johnson', 'phone' => '011-555-0101', 'email' => 'mike@aroundtheclock.co.za'],
        ['name' => 'Rapid Delivery Services', 'contact_person' => 'Sarah Martinez', 'phone' => '011-555-0202', 'email' => 'sarah@rapiddelivery.co.za'],
        ['name' => 'Cross Country Logistics', 'contact_person' => 'Tom Anderson', 'phone' => '011-555-0303', 'email' => 'tom@crosscountry.co.za'],
        ['name' => 'Premier Freight Solutions', 'contact_person' => 'Jennifer Brown', 'phone' => '011-555-0404', 'email' => 'jennifer@premierfreight.co.za'],
        ['name' => 'Express Cargo Movers', 'contact_person' => 'Robert Wilson', 'phone' => '011-555-0505', 'email' => 'robert@expresscargo.co.za'],
        ['name' => 'Safe & Sound Transport', 'contact_person' => 'Linda Davis', 'phone' => '011-555-0606', 'email' => 'linda@safesound.co.za'],
        ['name' => 'Mile High Logistics', 'contact_person' => 'James Miller', 'phone' => '011-555-0707', 'email' => 'james@milehigh.co.za'],
        ['name' => 'Reliable Routes Transport', 'contact_person' => 'Patricia Garcia', 'phone' => '011-555-0808', 'email' => 'patricia@reliableroutes.co.za']
    ];
    
    $inserted = 0;
    $skipped = 0;
    $errors = [];
    
    // Check if contractors table exists, create it if it doesn't
    $tableExists = $db->fetchOne("SHOW TABLES LIKE 'contractors'");
    if (!$tableExists) {
        // Create contractors table
        $createTableSQL = "
            CREATE TABLE IF NOT EXISTS contractors (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                contact_person VARCHAR(255),
                phone VARCHAR(50),
                email VARCHAR(255),
                status ENUM('active', 'inactive') DEFAULT 'active',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )
        ";
        try {
            $db->query($createTableSQL);
            $tableCreated = true;
        } catch (Exception $e) {
            die('<h2 style="color: red;">Error creating contractors table: ' . htmlspecialchars($e->getMessage()) . '</h2>');
        }
    } else {
        $tableCreated = false;
    }
    
    // Insert each contractor
    foreach ($contractors as $contractor) {
        // Check if contractor already exists
        $existing = $db->fetchOne("SELECT id FROM contractors WHERE name = ?", [$contractor['name']]);
        
        if (!$existing) {
            // Insert new contractor
            $contractorData = [
                'name' => $contractor['name'],
                'contact_person' => $contractor['contact_person'],
                'phone' => $contractor['phone'],
                'email' => $contractor['email'],
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            $result = $db->insert('contractors', $contractorData);
            if ($result) {
                $inserted++;
            } else {
                $errors[] = $contractor['name'];
            }
        } else {
            $skipped++;
        }
    }
    
    // Get all active contractors
    $allContractors = $db->fetchAll("SELECT * FROM contractors WHERE status = 'active' ORDER BY name");
    
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Add Contractors - Logistics App</title>
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
            h1 {
                color: #007cba;
                border-bottom: 2px solid #007cba;
                padding-bottom: 10px;
            }
            .success {
                color: #28a745;
                font-weight: bold;
                margin: 15px 0;
            }
            .info {
                color: #17a2b8;
                margin: 15px 0;
            }
            .error {
                color: #dc3545;
                margin: 15px 0;
            }
            table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 20px;
            }
            th, td {
                padding: 12px;
                text-align: left;
                border-bottom: 1px solid #ddd;
            }
            th {
                background: #007cba;
                color: white;
            }
            tr:hover {
                background: #f8f9fa;
            }
            .btn {
                display: inline-block;
                padding: 10px 20px;
                background: #007cba;
                color: white;
                text-decoration: none;
                border-radius: 4px;
                margin-top: 20px;
            }
            .btn:hover {
                background: #005a87;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>Add Sample Contractors</h1>
            
            <?php if (isset($tableCreated) && $tableCreated): ?>
                <div class="success">✓ Contractors table created successfully</div>
            <?php endif; ?>
            
            <?php if ($inserted > 0): ?>
                <div class="success">✓ Successfully inserted <?php echo $inserted; ?> contractor(s)</div>
            <?php endif; ?>
            
            <?php if ($skipped > 0): ?>
                <div class="info">ℹ Skipped <?php echo $skipped; ?> contractor(s) (already exist)</div>
            <?php endif; ?>
            
            <?php if (!empty($errors)): ?>
                <div class="error">✗ Error inserting: <?php echo implode(', ', $errors); ?></div>
            <?php endif; ?>
            
            <?php if (count($allContractors) > 0): ?>
                <h2>Active Contractors (<?php echo count($allContractors); ?>)</h2>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Contact Person</th>
                            <th>Phone</th>
                            <th>Email</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($allContractors as $contractor): ?>
                            <tr>
                                <td><?php echo $contractor['id']; ?></td>
                                <td><strong><?php echo htmlspecialchars($contractor['name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($contractor['contact_person'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($contractor['phone'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($contractor['email'] ?? '-'); ?></td>
                                <td><span style="color: green;"><?php echo ucfirst($contractor['status']); ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="info">No contractors found in database.</div>
            <?php endif; ?>
            
            <a href="?page=companies" class="btn">Go to Companies</a>
            <a href="index.php" class="btn" style="background: #6c757d;">Go to Dashboard</a>
        </div>
    </body>
    </html>
    <?php
    
} catch (Exception $e) {
    die('<h2 style="color: red;">Error: ' . htmlspecialchars($e->getMessage()) . '</h2>');
}
?>

