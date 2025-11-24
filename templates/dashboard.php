<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - Dashboard</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <!-- Header -->
        <header class="header">
            <h1><?php echo APP_NAME; ?></h1>
            <nav class="nav">
                <a href="?page=dashboard" class="nav-link active">Dashboard</a>
                <a href="?page=companies" class="nav-link">Companies</a>
                <a href="?page=loadsheets" class="nav-link">Load Sheets</a>
                <a href="?page=invoices" class="nav-link">Invoices</a>
                <a href="?page=statements" class="nav-link">Statements</a>
            </nav>
        </header>

        <!-- Main Content -->
        <main class="main">
            <div class="dashboard-header">
                <h2>Dashboard Overview</h2>
                <p>Manage your logistics operations from this central hub</p>
            </div>

            <!-- Quick Stats -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">📄</div>
                    <div class="stat-content">
                        <h3><?php echo $stats['invoices_this_month']; ?></h3>
                        <p>Invoices This Month</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">💰</div>
                    <div class="stat-content">
                        <h3>R <?php echo number_format($stats['outstanding_balance'], 2); ?></h3>
                        <p>Outstanding Balance</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">🏢</div>
                    <div class="stat-content">
                        <h3><?php echo $stats['active_companies']; ?></h3>
                        <p>Active Companies</p>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="dashboard-grid">
                <!-- Recent Load Sheets -->
                <div class="dashboard-section">
                    <h3>Recent Load Sheets</h3>
                    <div class="section-content">
                        <?php if (!empty($recentLoadSheets)): ?>
                            <?php foreach ($recentLoadSheets as $loadSheet): ?>
                                <div class="load-sheet-item">
                                    <div class="item-info">
                                        <h4><?php echo htmlspecialchars($loadSheet['company_name']); ?></h4>
                                        <p><?php echo $loadSheet['pallet_quantity']; ?> pallets - R <?php echo number_format($loadSheet['final_rate'], 2); ?></p>
                                        <small><?php echo date('M j, Y', strtotime($loadSheet['created_at'])); ?></small>
                                    </div>
                                    <div class="item-actions">
                                        <?php if ($loadSheet['status'] === 'completed'): ?>
                                            <?php if (!isset($loadSheet['invoice_id']) || empty($loadSheet['invoice_id']) || $loadSheet['invoice_id'] == 0): ?>
                                                <button class="btn btn-primary create-invoice-btn" data-load-sheet-id="<?php echo $loadSheet['id']; ?>">
                                                    Create Invoice
                                                </button>
                                            <?php else: ?>
                                                <button class="btn btn-success" onclick="viewInvoice(<?php echo $loadSheet['invoice_id']; ?>)">
                                                    View Invoice (<?php echo htmlspecialchars($loadSheet['invoice_number']); ?>)
                                                </button>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="status-badge status-<?php echo $loadSheet['status']; ?>">
                                                <?php echo ucfirst($loadSheet['status']); ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="no-data">No load sheets found. <a href="?page=loadsheets">Create your first load sheet</a></p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Pending Invoices -->
                <div class="dashboard-section">
                    <h3>Pending Invoices</h3>
                    <div class="section-content">
                        <?php if (!empty($pendingInvoices)): ?>
                            <?php foreach ($pendingInvoices as $invoice): ?>
                                <div class="invoice-item">
                                    <div class="item-info">
                                        <h4><?php echo htmlspecialchars($invoice['company_name']); ?></h4>
                                        <p><?php echo $invoice['invoice_number']; ?> - R <?php echo number_format($invoice['total_amount'], 2); ?></p>
                                        <small>Due: <?php echo date('M j, Y', strtotime($invoice['due_date'])); ?></small>
                                    </div>
                                    <div class="item-actions">
                                        <button class="btn btn-success mark-paid-btn" data-invoice-id="<?php echo $invoice['id']; ?>">
                                            Mark Paid
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="no-data">No pending invoices</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="quick-actions">
                <h3>Quick Actions</h3>
                <div class="action-buttons">
                    <a href="?page=loadsheets&action=new" class="btn btn-primary">New Load Sheet</a>
                    <a href="?page=companies&action=new" class="btn btn-secondary">New Company</a>
                    <button class="btn btn-info" onclick="createSampleData()">Create Sample Data</button>
                </div>
            </div>
        </main>
    </div>

    <!-- Invoice Details Modal -->
    <div id="invoice-details-modal" class="modal" style="display: none;">
        <div class="modal-content large">
            <div class="modal-header">
                <h3>Invoice Details</h3>
                <button class="close-btn" onclick="closeInvoiceDetailsModal()">&times;</button>
            </div>
            <div id="invoice-details-content">
                <!-- Invoice details will be loaded here -->
            </div>
        </div>
    </div>

    <!-- Email Dialog (hidden by default) -->
    <div id="email-dialog" class="modal" style="display: none;">
        <div class="modal-content">
            <h3>Send Invoice to Customer</h3>
            <p id="invoice-details"></p>
            <div class="form-group">
                <label for="email-address">Email Address:</label>
                <input type="email" id="email-address" placeholder="customer@example.com">
            </div>
            <div class="modal-actions">
                <button class="btn btn-secondary" onclick="closeEmailDialog()">Cancel</button>
                <button class="btn btn-primary" onclick="sendInvoiceEmail()">Send Invoice</button>
            </div>
        </div>
    </div>

    <script src="assets/js/app.js"></script>
</body>
</html>
