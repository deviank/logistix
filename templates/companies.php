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
                    <h3>Active Companies</h3>
                    <div class="section-actions">
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
                                    <button class="btn btn-sm btn-primary" onclick="editCompany(<?php echo $company['id']; ?>)">Edit</button>
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

    <script src="assets/js/app.js"></script>
</body>
</html>
