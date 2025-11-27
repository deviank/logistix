<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - Load Sheets</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <!-- Header -->
        <header class="header">
            <h1><?php echo APP_NAME; ?></h1>
            <nav class="nav">
                <a href="?page=dashboard" class="nav-link">Dashboard</a>
                <a href="?page=companies" class="nav-link">Companies</a>
                <a href="?page=loadsheets" class="nav-link active">Load Sheets</a>
                <a href="?page=invoices" class="nav-link">Invoices</a>
                <a href="?page=statements" class="nav-link">Statements</a>
            </nav>
        </header>

        <!-- Main Content -->
        <main class="main">
            <div class="page-header">
                <h2>Load Sheet Management</h2>
                <p>Create and manage load sheets for your logistics operations</p>
                <button class="btn btn-primary" onclick="showAddLoadSheetForm()">New Load Sheet</button>
            </div>

            <!-- Load Sheets List -->
            <div class="content-section">
                <div class="section-header">
                    <h3>All Load Sheets</h3>
                    <div class="section-actions">
                        <select id="status-filter" class="filter-select">
                            <option value="">All Status</option>
                            <option value="pending">Pending</option>
                            <option value="in_progress">In Progress</option>
                            <option value="completed">Completed</option>
                        </select>
                        <input type="text" id="loadsheet-search" placeholder="Search load sheets..." class="search-input">
                    </div>
                </div>

                <div class="loadsheets-table">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Company</th>
                                <th>Pallets</th>
                                <th>Description</th>
                                <th>Rate</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($loadSheets)): ?>
                                <?php foreach ($loadSheets as $loadSheet): ?>
                                    <tr data-loadsheet-id="<?php echo $loadSheet['id']; ?>" 
                                        class="loadsheet-row"
                                        data-status="<?php echo htmlspecialchars($loadSheet['status']); ?>"
                                        data-company="<?php echo htmlspecialchars(strtolower($loadSheet['company_name'])); ?>"
                                        data-description="<?php echo htmlspecialchars(strtolower($loadSheet['cargo_description'] ?? '')); ?>"
                                        data-date="<?php echo date('Y-m-d', strtotime($loadSheet['created_at'])); ?>">
                                        <td><?php echo date('M j, Y', strtotime($loadSheet['created_at'])); ?></td>
                                        <td><?php echo htmlspecialchars($loadSheet['company_name']); ?></td>
                                        <td><?php echo $loadSheet['pallet_quantity']; ?></td>
                                        <td><?php echo htmlspecialchars($loadSheet['cargo_description']); ?></td>
                                        <td>R <?php echo number_format($loadSheet['final_rate'], 2); ?></td>
                                        <td>
                                            <span class="status-badge status-<?php echo $loadSheet['status']; ?>">
                                                <?php echo ucfirst($loadSheet['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <button class="btn btn-sm btn-info" onclick="viewLoadSheet(<?php echo $loadSheet['id']; ?>)">View</button>
                                                <?php if ($loadSheet['status'] === 'completed'): ?>
                                                    <?php if (!isset($loadSheet['invoice_id']) || empty($loadSheet['invoice_id']) || $loadSheet['invoice_id'] == 0): ?>
                                                        <button class="btn btn-sm btn-primary create-invoice-btn" data-load-sheet-id="<?php echo $loadSheet['id']; ?>">
                                                            Create Invoice
                                                        </button>
                                                    <?php else: ?>
                                                        <a href="?page=invoices" class="btn btn-sm btn-success">
                                                            Invoice: <?php echo htmlspecialchars($loadSheet['invoice_number']); ?>
                                                        </a>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <button class="btn btn-sm btn-warning" onclick="editLoadSheet(<?php echo $loadSheet['id']; ?>)">Edit</button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="no-data">No load sheets found. <button class="btn btn-primary" onclick="showAddLoadSheetForm()">Create your first load sheet</button></td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- Add/Edit Load Sheet Modal -->
    <div id="loadsheet-modal" class="modal" style="display: none;">
        <div class="modal-content large">
            <div class="modal-header">
                <h3 id="loadsheet-modal-title">New Load Sheet</h3>
                <button class="close-btn" onclick="closeLoadSheetModal()">&times;</button>
            </div>
            <form id="loadsheet-form">
                <input type="hidden" id="loadsheet-id" name="loadsheet_id">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="loadsheet-company">Company *</label>
                        <select id="loadsheet-company" name="company_id" required onchange="loadCompanyDetails()">
                            <option value="">Select Company</option>
                            <?php
                            // Get companies for dropdown
                            $companies = $this->db->fetchAll("SELECT * FROM companies WHERE status = 'active' ORDER BY name");
                            foreach ($companies as $company):
                            ?>
                                <option value="<?php echo $company['id']; ?>" 
                                        data-rate="<?php echo $company['rate_per_pallet']; ?>"
                                        data-payment-terms="<?php echo $company['payment_terms']; ?>">
                                    <?php echo htmlspecialchars($company['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="loadsheet-date">Date *</label>
                        <input type="date" id="loadsheet-date" name="date" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="pallet-quantity">Pallet Quantity *</label>
                        <input type="number" id="pallet-quantity" name="pallet_quantity" min="1" required onchange="calculateTotal()">
                    </div>
                    <div class="form-group">
                        <label for="rate-per-pallet">Rate per Pallet (R) *</label>
                        <input type="number" id="rate-per-pallet" name="rate_per_pallet" step="0.01" required onchange="calculateTotal()">
                    </div>
                </div>

                <div class="form-group">
                    <label for="cargo-description">Cargo Description</label>
                    <textarea id="cargo-description" name="cargo_description" rows="3" placeholder="Describe the cargo being transported"></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="delivery-method">Delivery Method *</label>
                        <select id="delivery-method" name="delivery_method" required onchange="toggleContractorFields()">
                            <option value="">Select Method</option>
                            <option value="own_driver">Own Driver</option>
                            <option value="contractor">Contractor</option>
                        </select>
                    </div>
                    <div class="form-group" id="contractor-cost-group" style="display: none;">
                        <label for="contractor-cost">Contractor Cost (R)</label>
                        <input type="number" id="contractor-cost" name="contractor_cost" step="0.01" onchange="calculateProfit()">
                    </div>
                </div>

                <div class="form-group">
                    <label for="loadsheet-status">Status</label>
                    <select id="loadsheet-status" name="status">
                        <option value="pending">Pending</option>
                        <option value="in_progress">In Progress</option>
                        <option value="completed">Completed</option>
                    </select>
                </div>

                <!-- Calculation Summary -->
                <div class="calculation-summary">
                    <h4>Financial Summary</h4>
                    <div class="calc-row">
                        <span>Subtotal:</span>
                        <span id="calc-subtotal">R 0.00</span>
                    </div>
                    <div class="calc-row">
                        <span>Contractor Cost:</span>
                        <span id="calc-contractor-cost">R 0.00</span>
                    </div>
                    <div class="calc-row total">
                        <span>Net Profit:</span>
                        <span id="calc-profit">R 0.00</span>
                    </div>
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeLoadSheetModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Load Sheet</button>
                </div>
            </form>
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
