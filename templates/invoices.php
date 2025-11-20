<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - Invoices</title>
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
                <a href="?page=loadsheets" class="nav-link">Load Sheets</a>
                <a href="?page=invoices" class="nav-link active">Invoices</a>
                <a href="?page=statements" class="nav-link">Statements</a>
            </nav>
        </header>

        <!-- Main Content -->
        <main class="main">
            <div class="page-header">
                <h2>Invoice Management</h2>
                <p>View and manage all invoices and payments</p>
            </div>

            <!-- Invoice Filters -->
            <div class="filters-section">
                <div class="filter-group">
                    <label for="status-filter">Payment Status:</label>
                    <select id="status-filter" class="filter-select">
                        <option value="">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="paid">Paid</option>
                        <option value="overdue">Overdue</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="date-filter">Date Range:</label>
                    <select id="date-filter" class="filter-select">
                        <option value="">All Time</option>
                        <option value="this_month">This Month</option>
                        <option value="last_month">Last Month</option>
                        <option value="this_quarter">This Quarter</option>
                        <option value="this_year">This Year</option>
                    </select>
                </div>
                <div class="filter-group">
                    <input type="text" id="invoice-search" placeholder="Search invoices..." class="search-input">
                </div>
            </div>

            <!-- Invoices List -->
            <div class="content-section">
                <div class="section-header">
                    <h3>All Invoices</h3>
                    <div class="section-actions">
                        <button class="btn btn-secondary" onclick="exportInvoices()">Export</button>
                        <button class="btn btn-primary" onclick="generateMonthlyStatement()">Generate Statement</button>
                    </div>
                </div>

                <div class="invoices-table">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Invoice #</th>
                                <th>Date</th>
                                <th>Company</th>
                                <th>Description</th>
                                <th>Pallets</th>
                                <th>Subtotal</th>
                                <th>VAT</th>
                                <th>Total</th>
                                <th>Due Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($invoices)): ?>
                                <?php foreach ($invoices as $invoice): ?>
                                    <?php 
                                    $dueDate = strtotime($invoice['due_date']);
                                    $isOverdue = $dueDate < time() && $invoice['payment_status'] === 'pending';
                                    $displayStatus = $isOverdue ? 'overdue' : $invoice['payment_status'];
                                    $invoiceDate = strtotime($invoice['invoice_date']);
                                    ?>
                                    <tr data-invoice-id="<?php echo $invoice['id']; ?>" 
                                        class="invoice-row" 
                                        data-status="<?php echo htmlspecialchars($displayStatus); ?>"
                                        data-invoice-date="<?php echo date('Y-m-d', $invoiceDate); ?>"
                                        data-company="<?php echo htmlspecialchars(strtolower($invoice['company_name'])); ?>"
                                        data-invoice-number="<?php echo htmlspecialchars(strtolower($invoice['invoice_number'])); ?>"
                                        data-description="<?php echo htmlspecialchars(strtolower($invoice['cargo_description'])); ?>">
                                        <td>
                                            <a href="#" onclick="viewInvoice(<?php echo $invoice['id']; ?>); return false;">
                                                <?php echo htmlspecialchars($invoice['invoice_number']); ?>
                                            </a>
                                        </td>
                                        <td><?php echo date('M j, Y', $invoiceDate); ?></td>
                                        <td><?php echo htmlspecialchars($invoice['company_name']); ?></td>
                                        <td><?php echo htmlspecialchars($invoice['cargo_description']); ?></td>
                                        <td><?php echo $invoice['pallet_quantity']; ?></td>
                                        <td>R <?php echo number_format($invoice['subtotal'], 2); ?></td>
                                        <td>R <?php echo number_format($invoice['vat_amount'], 2); ?></td>
                                        <td><strong>R <?php echo number_format($invoice['total_amount'], 2); ?></strong></td>
                                        <td>
                                            <span class="<?php echo $isOverdue ? 'overdue' : ''; ?>">
                                                <?php echo date('M j, Y', $dueDate); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="status-badge status-<?php echo $displayStatus; ?>">
                                                <?php echo ucfirst($displayStatus); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <button class="btn btn-sm btn-info" onclick="viewInvoice(<?php echo $invoice['id']; ?>)">View</button>
                                                <button class="btn btn-sm btn-secondary" onclick="downloadInvoice(<?php echo $invoice['id']; ?>)">PDF</button>
                                                <button class="btn btn-sm btn-warning" onclick="generateStatementFromInvoice(<?php echo $invoice['id']; ?>, <?php echo $invoice['company_id']; ?>, '<?php echo date('Y-m', strtotime($invoice['invoice_date'])); ?>')" title="Generate Statement for this Company">Statement</button>
                                                <?php if ($invoice['payment_status'] === 'pending' || $isOverdue): ?>
                                                    <button class="btn btn-sm btn-success mark-paid-btn" data-invoice-id="<?php echo $invoice['id']; ?>">Mark Paid</button>
                                                    <button class="btn btn-sm btn-primary send-email-btn" data-invoice-id="<?php echo $invoice['id']; ?>">Send Email</button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="11" class="no-data">No invoices found. <a href="?page=loadsheets">Create a load sheet first</a></td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Summary Section -->
                <div class="invoice-summary">
                    <div class="summary-cards">
                        <div class="summary-card">
                            <h4>Total Invoices</h4>
                            <p class="summary-number"><?php echo count($invoices); ?></p>
                        </div>
                        <div class="summary-card">
                            <h4>Pending Amount</h4>
                            <p class="summary-number">
                                R <?php 
                                $pendingTotal = 0;
                                foreach ($invoices as $invoice) {
                                    if ($invoice['payment_status'] === 'pending') {
                                        $pendingTotal += $invoice['total_amount'];
                                    }
                                }
                                echo number_format($pendingTotal, 2);
                                ?>
                            </p>
                        </div>
                        <div class="summary-card">
                            <h4>Paid Amount</h4>
                            <p class="summary-number">
                                R <?php 
                                $paidTotal = 0;
                                foreach ($invoices as $invoice) {
                                    if ($invoice['payment_status'] === 'paid') {
                                        $paidTotal += $invoice['total_amount'];
                                    }
                                }
                                echo number_format($paidTotal, 2);
                                ?>
                            </p>
                        </div>
                    </div>
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

    <!-- Email Dialog -->
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

    <!-- Generate Statement Modal -->
    <div id="statement-modal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Generate Monthly Statement</h3>
                <button class="close-btn" onclick="closeStatementModal()">&times;</button>
            </div>
            <form id="statement-form">
                <div class="form-group">
                    <label for="statement-company">Company:</label>
                    <select id="statement-company" name="company_id" required>
                        <option value="">Select Company</option>
                        <?php
                        $companies = $this->db->fetchAll("SELECT * FROM companies WHERE status = 'active' ORDER BY name");
                        foreach ($companies as $company):
                        ?>
                            <option value="<?php echo $company['id']; ?>">
                                <?php echo htmlspecialchars($company['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="statement-month">Month:</label>
                    <input type="month" id="statement-month" name="month" value="<?php echo date('Y-m'); ?>" required>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeStatementModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Generate Statement</button>
                </div>
            </form>
        </div>
    </div>

    <script src="assets/js/app.js"></script>
</body>
</html>
