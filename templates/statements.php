<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - Statements</title>
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
                <a href="?page=invoices" class="nav-link">Invoices</a>
                <a href="?page=statements" class="nav-link active">Statements</a>
            </nav>
        </header>

        <!-- Main Content -->
        <main class="main">
            <div class="page-header">
                <h2>Monthly Statements</h2>
                <p>Generate and manage monthly statements for your customers</p>
                <button class="btn btn-primary" onclick="showGenerateStatementForm()">Generate New Statement</button>
            </div>

            <!-- Statement Filters -->
            <div class="filters-section">
                <div class="filter-group">
                    <label for="company-filter">Company:</label>
                    <select id="company-filter" class="filter-select">
                        <option value="">All Companies</option>
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
                <div class="filter-group">
                    <label for="year-filter">Year:</label>
                    <select id="year-filter" class="filter-select">
                        <option value="">All Years</option>
                        <?php
                        $currentYear = date('Y');
                        for ($year = $currentYear; $year >= $currentYear - 5; $year--):
                        ?>
                            <option value="<?php echo $year; ?>"><?php echo $year; ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="filter-group">
                    <input type="text" id="statement-search" placeholder="Search statements..." class="search-input">
                </div>
            </div>

            <!-- Statements List -->
            <div class="content-section">
                <div class="section-header">
                    <h3>Generated Statements</h3>
                    <div class="section-actions">
                        <button class="btn btn-secondary" onclick="exportStatements()">Export All</button>
                    </div>
                </div>

                <div class="statements-table">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Statement #</th>
                                <th>Date</th>
                                <th>Company</th>
                                <th>Period</th>
                                <th>Invoices</th>
                                <th>Total Amount</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($statements)): ?>
                                <?php foreach ($statements as $statement): ?>
                                    <tr data-statement-id="<?php echo $statement['id']; ?>">
                                        <td>
                                            <a href="#" onclick="viewStatement(<?php echo $statement['id']; ?>); return false;">
                                                <?php echo htmlspecialchars($statement['statement_number']); ?>
                                            </a>
                                        </td>
                                        <td><?php echo date('M j, Y', strtotime($statement['created_at'])); ?></td>
                                        <td><?php echo htmlspecialchars($statement['company_name']); ?></td>
                                        <td>
                                            <?php echo date('M Y', strtotime($statement['statement_month'] . '-01')); ?>
                                        </td>
                                        <td><?php echo $statement['invoice_count']; ?></td>
                                        <td><strong>R <?php echo number_format($statement['total_amount'], 2); ?></strong></td>
                                        <td>
                                            <span class="status-badge status-<?php echo $statement['status']; ?>">
                                                <?php echo ucfirst($statement['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <button class="btn btn-sm btn-info" onclick="viewStatement(<?php echo $statement['id']; ?>)">View</button>
                                                <button class="btn btn-sm btn-secondary" onclick="downloadStatement(<?php echo $statement['id']; ?>)">PDF</button>
                                                <button class="btn btn-sm btn-primary" onclick="sendStatementEmail(<?php echo $statement['id']; ?>)">Send Email</button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="no-data">No statements found. <button class="btn btn-primary" onclick="showGenerateStatementForm()">Generate your first statement</button></td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Summary Section -->
                <div class="statement-summary">
                    <div class="summary-cards">
                        <div class="summary-card">
                            <h4>Total Statements</h4>
                            <p class="summary-number"><?php echo count($statements); ?></p>
                        </div>
                        <div class="summary-card">
                            <h4>This Month</h4>
                            <p class="summary-number">
                                <?php 
                                $thisMonth = 0;
                                foreach ($statements as $statement) {
                                    if (date('Y-m', strtotime($statement['created_at'])) === date('Y-m')) {
                                        $thisMonth++;
                                    }
                                }
                                echo $thisMonth;
                                ?>
                            </p>
                        </div>
                        <div class="summary-card">
                            <h4>Total Value</h4>
                            <p class="summary-number">
                                R <?php 
                                $totalValue = 0;
                                foreach ($statements as $statement) {
                                    $totalValue += $statement['total_amount'];
                                }
                                echo number_format($totalValue, 2);
                                ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Generate Statement Modal -->
    <div id="generate-statement-modal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Generate Monthly Statement</h3>
                <button class="close-btn" onclick="closeGenerateStatementModal()">&times;</button>
            </div>
            <form id="generate-statement-form">
                <div class="form-group">
                    <label for="statement-company">Company *</label>
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
                    <label for="statement-month">Month *</label>
                    <input type="month" id="statement-month" name="month" value="<?php echo date('Y-m'); ?>" required>
                </div>
                <div class="form-group">
                    <label for="statement-notes">Notes</label>
                    <textarea id="statement-notes" name="notes" rows="3" placeholder="Additional notes for the statement"></textarea>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeGenerateStatementModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Generate Statement</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Statement Details Modal -->
    <div id="statement-details-modal" class="modal" style="display: none;">
        <div class="modal-content large">
            <div class="modal-header">
                <h3>Statement Details</h3>
                <button class="close-btn" onclick="closeStatementDetailsModal()">&times;</button>
            </div>
            <div id="statement-details-content">
                <!-- Statement details will be loaded here -->
            </div>
        </div>
    </div>

    <!-- Email Dialog -->
    <div id="email-dialog" class="modal" style="display: none;">
        <div class="modal-content">
            <h3>Send Statement to Customer</h3>
            <p id="statement-details"></p>
            <div class="form-group">
                <label for="email-address">Email Address:</label>
                <input type="email" id="email-address" placeholder="customer@example.com">
            </div>
            <div class="modal-actions">
                <button class="btn btn-secondary" onclick="closeEmailDialog()">Cancel</button>
                <button class="btn btn-primary" onclick="sendStatementEmail()">Send Statement</button>
            </div>
        </div>
    </div>

    <script src="assets/js/app.js"></script>
</body>
</html>
