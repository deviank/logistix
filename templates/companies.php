<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - Companies</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <!-- Header -->
        <header class="header">
            <h1><?php echo APP_NAME; ?></h1>
            <nav class="nav">
                <a href="?page=dashboard" class="nav-link">Dashboard</a>
                <a href="?page=companies" class="nav-link active">Companies</a>
                <a href="?page=loadsheets" class="nav-link">Load Sheets</a>
                <a href="?page=invoices" class="nav-link">Invoices</a>
                <a href="?page=statements" class="nav-link">Statements</a>
            </nav>
        </header>

        <!-- Main Content -->
        <main class="main">
            <div class="page-header">
                <h2>Company Management</h2>
                <p>Manage your customer companies and their billing information</p>
                <button class="btn btn-primary" onclick="showAddCompanyForm()">Add New Company</button>
            </div>

            <!-- Companies List -->
            <div class="content-section">
                <div class="section-header">
                    <h3 id="companies-header"><?php echo $showInactive ?? false ? 'All Companies' : 'Active Companies'; ?></h3>
                    <div class="section-actions">
                        <button class="btn btn-sm btn-secondary" id="toggle-inactive-btn" onclick="toggleInactiveCompanies()" style="margin-right: 10px;">
                            <?php echo ($showInactive ?? false) ? 'Hide Inactive' : 'Show Inactive'; ?>
                        </button>
                        <input type="text" id="company-search" placeholder="Search companies..." class="search-input">
                    </div>
                </div>

                <div class="companies-grid">
                    <?php if (!empty($companies)): ?>
                        <?php foreach ($companies as $company): ?>
                            <div class="company-card" data-company-id="<?php echo $company['id']; ?>">
                                <div class="company-header">
                                    <h4><?php echo htmlspecialchars($company['name']); ?></h4>
                                    <span class="status-badge status-<?php echo $company['status']; ?>">
                                        <?php echo ucfirst($company['status']); ?>
                                    </span>
                                </div>
                                <div class="company-details">
                                    <div class="detail-item">
                                        <strong>Contact:</strong> <?php echo htmlspecialchars($company['contact_person']); ?>
                                    </div>
                                    <div class="detail-item">
                                        <strong>Email:</strong> <?php echo htmlspecialchars($company['email']); ?>
                                    </div>
                                    <div class="detail-item">
                                        <strong>Phone:</strong> <?php echo htmlspecialchars($company['phone']); ?>
                                    </div>
                                    <div class="detail-item">
                                        <strong>Payment Terms:</strong> <?php echo $company['payment_terms']; ?> days
                                    </div>
                                    <div class="detail-item">
                                        <strong>Rate per Pallet:</strong> R <?php echo number_format($company['rate_per_pallet'], 2); ?>
                                    </div>
                                </div>
                                <div class="company-actions">
                                    <?php if ($company['status'] === 'active'): ?>
                                        <button class="btn btn-sm btn-primary" onclick="createLoadSheetForCompany(<?php echo $company['id']; ?>, '<?php echo htmlspecialchars($company['name']); ?>', <?php echo $company['rate_per_pallet']; ?>, <?php echo $company['payment_terms']; ?>)">
                                            New Load Sheet
                                        </button>
                                    <?php endif; ?>
                                    <button class="btn btn-sm btn-info" onclick="editCompany(<?php echo $company['id']; ?>)">Edit</button>
                                    <button class="btn btn-sm btn-secondary" onclick="viewCompanyDetails(<?php echo $company['id']; ?>)">Details</button>
                                    <?php if ($company['status'] === 'active'): ?>
                                        <button class="btn btn-sm btn-warning" onclick="deactivateCompany(<?php echo $company['id']; ?>)">Deactivate</button>
                                    <?php else: ?>
                                        <button class="btn btn-sm btn-success" onclick="activateCompany(<?php echo $company['id']; ?>)">Activate</button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="no-data">
                            <p>No companies found. <button class="btn btn-primary" onclick="showAddCompanyForm()">Add your first company</button></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <!-- Add/Edit Company Modal -->
    <div id="company-modal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modal-title">Add New Company</h3>
                <button class="close-btn" onclick="closeCompanyModal()">&times;</button>
            </div>
            <form id="company-form">
                <input type="hidden" id="company-id" name="company_id">
                <div class="form-group">
                    <label for="company-name">Company Name *</label>
                    <input type="text" id="company-name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="contact-person">Contact Person *</label>
                    <input type="text" id="contact-person" name="contact_person" required>
                </div>
                <div class="form-group">
                    <label for="company-email">Email *</label>
                    <input type="email" id="company-email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="company-phone">Phone *</label>
                    <input type="tel" id="company-phone" name="phone" required>
                </div>
                <div class="form-group">
                    <label for="company-address">Address</label>
                    <textarea id="company-address" name="address" rows="3"></textarea>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="rate-per-pallet">Rate per Pallet (R) *</label>
                        <input type="number" id="rate-per-pallet" name="rate_per_pallet" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label for="payment-terms">Payment Terms (days) *</label>
                        <input type="number" id="payment-terms" name="payment_terms" value="30" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="company-status">Status</label>
                    <select id="company-status" name="status">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeCompanyModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Company</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Load Sheet Modal (for quick creation from company) -->
    <div id="loadsheet-modal" class="modal" style="display: none;">
        <div class="modal-content large">
            <div class="modal-header">
                <h3 id="loadsheet-modal-title">New Load Sheet</h3>
                <button class="close-btn" onclick="closeLoadSheetModal()">&times;</button>
            </div>
            <div class="modal-body">
            <form id="loadsheet-form">
                <input type="hidden" id="loadsheet-id" name="loadsheet_id">
                <input type="hidden" id="loadsheet-company-id" name="company_id">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="loadsheet-company">Company *</label>
                        <input type="text" id="loadsheet-company" name="company_name" readonly style="background: #f8f9fa; color: #666;">
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
                    <div class="form-group" id="contractor-select-group" style="display: none;">
                        <label for="contractor-select">Contractor *</label>
                        <div style="display: flex; gap: 8px; align-items: flex-end;">
                            <select id="contractor-select" name="contractor_id" style="flex: 1;" onchange="handleContractorSelection()">
                                <option value="">Select Contractor</option>
                            </select>
                            <button type="button" class="btn btn-sm" onclick="showAddContractorModal()" style="white-space: nowrap; padding: 8px 12px; height: fit-content;">+ Add New</button>
                        </div>
                    </div>
                </div>
                <div class="form-row">
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
    </div>

    <!-- Add New Contractor Modal -->
    <div id="contractor-modal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Add New Contractor</h3>
                <button class="close-btn" onclick="closeContractorModal()">&times;</button>
            </div>
            <form id="contractor-form">
                <div class="form-group">
                    <label for="contractor-name">Contractor Name *</label>
                    <input type="text" id="contractor-name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="contractor-contact">Contact Person</label>
                    <input type="text" id="contractor-contact" name="contact_person">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="contractor-phone">Phone</label>
                        <input type="text" id="contractor-phone" name="phone">
                    </div>
                    <div class="form-group">
                        <label for="contractor-email">Email</label>
                        <input type="email" id="contractor-email" name="email">
                    </div>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeContractorModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Contractor</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Company Details Modal -->
    <div id="company-details-modal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Company Details</h3>
                <button class="close-btn" onclick="closeCompanyDetailsModal()">&times;</button>
            </div>
            <div class="modal-body" id="company-details-content" style="max-height: 70vh; overflow-y: auto;">
                <!-- Content will be loaded dynamically -->
            </div>
        </div>
    </div>

    <script src="assets/js/app.js"></script>
</body>
</html>
