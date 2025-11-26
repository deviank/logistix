/**
 * Logistics App JavaScript
 */

// Global variables
let currentInvoiceData = null;

// Initialize app when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    initializeApp();
});

function initializeApp() {
    // Initialize event listeners
    initializeEventListeners();
    
    // Load contractors when app initializes
    loadContractors();
    
    // Auto-refresh stats every 5 minutes
    setInterval(refreshStats, 300000);
}

function initializeEventListeners() {
    // Create invoice buttons
    document.querySelectorAll('.create-invoice-btn').forEach(button => {
        button.addEventListener('click', function() {
            const loadSheetId = this.getAttribute('data-load-sheet-id');
            createInvoice(loadSheetId);
        });
    });
    
    // Mark paid buttons
    document.querySelectorAll('.mark-paid-btn').forEach(button => {
        button.addEventListener('click', function() {
            const invoiceId = this.getAttribute('data-invoice-id');
            markInvoicePaid(invoiceId);
        });
    });
    
    // Send email buttons
    document.querySelectorAll('.send-email-btn').forEach(button => {
        button.addEventListener('click', function() {
            const invoiceId = this.getAttribute('data-invoice-id');
            sendInvoiceEmailFromButton(invoiceId);
        });
    });
    
    // Load sheet form submission
    const loadSheetForm = document.getElementById('loadsheet-form');
    if (loadSheetForm) {
        loadSheetForm.addEventListener('submit', handleLoadSheetSubmit);
    }
    
    // Company form submission
    const companyForm = document.getElementById('company-form');
    if (companyForm) {
        companyForm.addEventListener('submit', handleCompanySubmit);
    }
    
    // Contractor form submission
    const contractorForm = document.getElementById('contractor-form');
    if (contractorForm) {
        contractorForm.addEventListener('submit', handleContractorSubmit);
    }
    
    // Statement form submission (for generate-statement-form)
    const generateStatementForm = document.getElementById('generate-statement-form');
    if (generateStatementForm) {
        generateStatementForm.addEventListener('submit', handleGenerateStatementSubmit);
    }
    
    // Statement form submission (for statement-form in invoices page)
    const statementForm = document.getElementById('statement-form');
    if (statementForm) {
        statementForm.addEventListener('submit', handleGenerateStatementSubmit);
    }
    
    // Invoice filtering
    const statusFilter = document.getElementById('status-filter');
    const dateFilter = document.getElementById('date-filter');
    const invoiceSearch = document.getElementById('invoice-search');
    
    if (statusFilter) {
        statusFilter.addEventListener('change', filterInvoices);
    }
    if (dateFilter) {
        dateFilter.addEventListener('change', filterInvoices);
    }
    if (invoiceSearch) {
        invoiceSearch.addEventListener('input', filterInvoices);
    }
}

function createInvoice(loadSheetId) {
    if (!confirm('Are you sure you want to create an invoice from this load sheet?')) {
        return;
    }
    
    const button = document.querySelector(`[data-load-sheet-id="${loadSheetId}"]`);
    const originalText = button.textContent;
    button.disabled = true;
    button.textContent = 'Creating...';
    
    console.log('Creating invoice for load sheet:', loadSheetId);
    
    fetch('?page=ajax', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=create_invoice&load_sheet_id=${loadSheetId}`
    })
    .then(response => response.json())
    .then(data => {
        console.log('Invoice creation response:', data);
        
        if (data.success) {
            showNotification(`Invoice ${data.invoice_number} created successfully!`, 'success');
            
            // Store invoice data for email dialog
            currentInvoiceData = data;
            
            // Show email dialog
            showEmailDialog(data);
            
            // Refresh page after a delay
            setTimeout(() => {
                location.reload();
            }, 2000);
        } else {
            showNotification('Error creating invoice: ' + data.message, 'error');
            button.disabled = false;
            button.textContent = originalText;
        }
    })
    .catch(error => {
        console.error('Error creating invoice:', error);
        showNotification('Error creating invoice. Please try again.', 'error');
        button.disabled = false;
        button.textContent = originalText;
    });
}

function markInvoicePaid(invoiceId) {
    if (!confirm('Are you sure you want to mark this invoice as paid?')) {
        return;
    }
    
    const button = document.querySelector(`[data-invoice-id="${invoiceId}"]`);
    const originalText = button.textContent;
    button.disabled = true;
    button.textContent = 'Updating...';
    
    console.log('Marking invoice as paid:', invoiceId);
    
    fetch('?page=ajax', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=mark_invoice_paid&invoice_id=${invoiceId}`
    })
    .then(response => response.json())
    .then(data => {
        console.log('Mark paid response:', data);
        
        if (data.success) {
            showNotification('Invoice marked as paid!', 'success');
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            showNotification('Error updating invoice: ' + data.message, 'error');
            button.disabled = false;
            button.textContent = originalText;
        }
    })
    .catch(error => {
        console.error('Error marking invoice as paid:', error);
        showNotification('Error updating invoice. Please try again.', 'error');
        button.disabled = false;
        button.textContent = originalText;
    });
}

function showEmailDialog(invoiceData) {
    const dialog = document.getElementById('email-dialog');
    const detailsElement = document.getElementById('invoice-details') || document.getElementById('statement-details');
    
    if (dialog && detailsElement) {
        detailsElement.innerHTML = `
            <p><strong>Invoice ${invoiceData.invoice_number || invoiceData.invoice?.invoice_number}</strong> has been created successfully!</p>
            <p>Total Amount: R ${parseFloat(invoiceData.total_amount || invoiceData.invoice?.total_amount || 0).toFixed(2)}</p>
        `;
        
        // Update dialog title
        const title = dialog.querySelector('h3');
        if (title) title.textContent = 'Send Invoice to Customer';
        
        // Update send button onclick
        const sendButton = dialog.querySelector('.btn-primary');
        if (sendButton) {
            sendButton.setAttribute('onclick', 'sendInvoiceEmail()');
            sendButton.textContent = 'Send Invoice';
        }
        
        dialog.style.display = 'flex';
        
        // Focus on email input
        setTimeout(() => {
            const emailInput = document.getElementById('email-address');
            if (emailInput) {
                emailInput.value = '';
                emailInput.focus();
            }
        }, 100);
    }
}

function closeEmailDialog() {
    const dialog = document.getElementById('email-dialog');
    dialog.style.display = 'none';
    currentInvoiceData = null;
    currentStatementData = null;
    
    // Reset email input
    const emailInput = document.getElementById('email-address');
    if (emailInput) emailInput.value = '';
    
    // Reset send button onclick to default (for invoices)
    const sendButton = dialog.querySelector('.btn-primary');
    if (sendButton) {
        sendButton.setAttribute('onclick', 'sendInvoiceEmail()');
    }
}

function sendInvoiceEmail() {
    const emailAddress = document.getElementById('email-address').value.trim();
    
    if (!emailAddress) {
        alert('Please enter an email address');
        return;
    }
    
    if (!currentInvoiceData) {
        alert('No invoice data available');
        return;
    }
    
    const sendButton = document.querySelector('#email-dialog .btn-primary');
    const originalText = sendButton.textContent;
    sendButton.disabled = true;
    sendButton.textContent = 'Sending...';
    
    console.log('Sending invoice email:', currentInvoiceData.invoice_id, emailAddress);
    
    fetch('?page=ajax', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=send_invoice_email&invoice_id=${currentInvoiceData.invoice_id}&email_address=${encodeURIComponent(emailAddress)}`
    })
    .then(response => response.json())
    .then(data => {
        console.log('Email send response:', data);
        
        if (data.success) {
            showNotification(`Invoice sent successfully to ${emailAddress}`, 'success');
            closeEmailDialog();
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            showNotification('Error sending invoice: ' + data.message, 'error');
            sendButton.disabled = false;
            sendButton.textContent = originalText;
        }
    })
    .catch(error => {
        console.error('Error sending invoice email:', error);
        showNotification('Error sending invoice. Please try again.', 'error');
        sendButton.disabled = false;
        sendButton.textContent = originalText;
    });
}

function sendInvoiceEmailFromButton(invoiceId) {
    // Load invoice details first
    fetch('?page=ajax&action=get_invoice_details&invoice_id=' + invoiceId)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.invoice) {
                currentInvoiceData = { invoice_id: invoiceId };
                showEmailDialog(data.invoice);
            } else {
                showNotification('Error loading invoice: ' + (data.message || 'Unknown error'), 'error');
            }
        })
        .catch(error => {
            console.error('Error loading invoice:', error);
            showNotification('Error loading invoice. Please try again.', 'error');
        });
}

function createSampleData() {
    if (!confirm('This will create sample data for testing. Continue?')) {
        return;
    }
    
    showNotification('Sample data already exists in the database!', 'info');
}

function generateDummyCompanies() {
    if (!confirm('This will generate random South African companies for testing purposes. Continue?')) {
        return;
    }
    
    // Show loading notification
    showNotification('Generating dummy companies...', 'info');
    
    fetch('?page=ajax&action=generate_dummy_companies', {
        method: 'POST'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(
                data.message || `Successfully generated ${data.created} companies!`, 
                'success'
            );
            // Reload the page after a short delay to show new companies
            setTimeout(() => {
                window.location.reload();
            }, 2000);
        } else {
            showNotification('Error: ' + data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error generating dummy companies', 'error');
    });
}

function removeDuplicateCompanies() {
    if (!confirm('This will remove duplicate companies (companies with the same name). The company with the most invoices/load sheets will be kept. This action cannot be undone. Continue?')) {
        return;
    }
    
    // Show loading notification
    showNotification('Removing duplicate companies...', 'info');
    
    fetch('?page=ajax&action=remove_duplicate_companies', {
        method: 'POST'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(
                data.message || `Removed ${data.deleted} duplicate companies.`, 
                'success'
            );
            // Reload the page after a short delay
            setTimeout(() => {
                window.location.reload();
            }, 2000);
        } else {
            showNotification('Error: ' + data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error removing duplicate companies', 'error');
    });
}

function generateDummyInvoiceData() {
    if (!confirm('This will generate random invoice history for the past 2 years for all active companies. This may take a moment. Continue?')) {
        return;
    }
    
    // Show loading notification
    showNotification('Generating invoice history... This may take a moment.', 'info');
    
    fetch('?page=ajax&action=generate_dummy_invoices', {
        method: 'POST'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(
                `Successfully generated ${data.invoices} invoices and ${data.load_sheets} load sheets for ${data.companies} companies!`, 
                'success'
            );
            // Reload the page after a short delay to show updated invoice counts
            setTimeout(() => {
                window.location.reload();
            }, 2000);
        } else {
            showNotification('Error: ' + data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error generating invoice history', 'error');
    });
}

// Company Management Functions
function showAddCompanyForm() {
    const modal = document.getElementById('company-modal');
    const form = document.getElementById('company-form');
    const title = document.getElementById('modal-title');
    
    // Reset form
    form.reset();
    document.getElementById('company-id').value = '';
    document.getElementById('payment-terms').value = '30'; // Reset to default
    document.getElementById('company-status').value = 'active'; // Reset to default
    title.textContent = 'Add New Company';
    
    modal.style.display = 'flex';
    
    // Focus on first input
    setTimeout(() => {
        document.getElementById('company-name').focus();
    }, 100);
}

function editCompany(companyId) {
    fetch('?page=ajax&action=get_company_details&company_id=' + companyId)
        .then(response => response.text())
        .then(text => {
            let data = null;
            try {
                data = JSON.parse(text);
            } catch (e) {
                console.error('Error parsing company data:', text);
                showNotification('Error loading company data', 'error');
                return;
            }
            
            if (data.success && data.company) {
                const company = data.company;
                const modal = document.getElementById('company-modal');
                const form = document.getElementById('company-form');
                const title = document.getElementById('modal-title');
                
                // Populate form fields
                document.getElementById('company-id').value = company.id;
                document.getElementById('company-name').value = company.name || '';
                document.getElementById('contact-person').value = company.contact_person || '';
                document.getElementById('company-email').value = company.email || '';
                document.getElementById('company-phone').value = company.phone || '';
                document.getElementById('company-address').value = company.billing_address || '';
                document.getElementById('rate-per-pallet').value = company.rate_per_pallet || '';
                document.getElementById('payment-terms').value = company.payment_terms || 30;
                document.getElementById('company-status').value = company.status || 'active';
                
                // Update modal title
                title.textContent = 'Edit Company';
                
                // Show modal
                modal.style.display = 'flex';
                
                // Focus on first input
                setTimeout(() => {
                    document.getElementById('company-name').focus();
                }, 100);
            } else {
                showNotification('Error loading company: ' + (data.message || 'Unknown error'), 'error');
            }
        })
        .catch(error => {
            console.error('Error loading company:', error);
            showNotification('Error loading company. Please try again.', 'error');
        });
}

function viewCompanyDetails(companyId) {
    fetch('?page=ajax&action=get_company_details&company_id=' + companyId)
        .then(response => response.text())
        .then(text => {
            let data = null;
            try {
                data = JSON.parse(text);
            } catch (e) {
                console.error('Error parsing company data:', text);
                showNotification('Error loading company details', 'error');
                return;
            }
            
            if (data.success && data.company) {
                const company = data.company;
                const modal = document.getElementById('company-details-modal');
                const content = document.getElementById('company-details-content');
                
                // Build details HTML
                content.innerHTML = `
                    <div style="line-height: 1.8;">
                        <div style="margin-bottom: 1rem;">
                            <strong>Company Name:</strong><br>
                            ${escapeHtml(company.name)}
                        </div>
                        <div style="margin-bottom: 1rem;">
                            <strong>Contact Person:</strong><br>
                            ${escapeHtml(company.contact_person || '-')}
                        </div>
                        <div style="margin-bottom: 1rem;">
                            <strong>Email:</strong><br>
                            <a href="mailto:${escapeHtml(company.email)}">${escapeHtml(company.email || '-')}</a>
                        </div>
                        <div style="margin-bottom: 1rem;">
                            <strong>Phone:</strong><br>
                            ${escapeHtml(company.phone || '-')}
                        </div>
                        <div style="margin-bottom: 1rem;">
                            <strong>Billing Address:</strong><br>
                            ${escapeHtml(company.billing_address || '-')}
                        </div>
                        <div style="margin-bottom: 1rem;">
                            <strong>Payment Terms:</strong><br>
                            ${company.payment_terms || 30} days
                        </div>
                        <div style="margin-bottom: 1rem;">
                            <strong>Rate per Pallet:</strong><br>
                            R ${parseFloat(company.rate_per_pallet || 0).toFixed(2)}
                        </div>
                        <div style="margin-bottom: 1rem;">
                            <strong>Status:</strong><br>
                            <span class="status-badge status-${company.status || 'active'}">${(company.status || 'active').charAt(0).toUpperCase() + (company.status || 'active').slice(1)}</span>
                        </div>
                        ${company.vat_number ? `
                        <div style="margin-bottom: 1rem;">
                            <strong>VAT Number:</strong><br>
                            ${escapeHtml(company.vat_number)}
                        </div>
                        ` : ''}
                        <div style="margin-bottom: 1rem;">
                            <strong>Total Invoices:</strong><br>
                            <span style="color: #007cba; font-weight: 600; font-size: 1.1rem;">${company.invoice_count || 0}</span>
                        </div>
                        <div style="margin-bottom: 1rem;">
                            <strong>Created:</strong><br>
                            ${company.created_at ? new Date(company.created_at).toLocaleDateString() : '-'}
                        </div>
                        ${company.recent_invoices && company.recent_invoices.length > 0 ? `
                        <div style="margin-top: 2rem; padding-top: 1.5rem; border-top: 2px solid #e0e0e0;">
                            <h4 style="color: #007cba; margin-bottom: 1rem;">Recent Invoice History</h4>
                            <table style="width: 100%; border-collapse: collapse; font-size: 0.9rem;">
                                <thead>
                                    <tr style="background: #f8f9fa;">
                                        <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Invoice #</th>
                                        <th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Date</th>
                                        <th style="border: 1px solid #ddd; padding: 8px; text-align: right;">Amount</th>
                                        <th style="border: 1px solid #ddd; padding: 8px; text-align: center;">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${company.recent_invoices.map(invoice => `
                                        <tr>
                                            <td style="border: 1px solid #ddd; padding: 8px;">${escapeHtml(invoice.invoice_number)}</td>
                                            <td style="border: 1px solid #ddd; padding: 8px;">${new Date(invoice.invoice_date).toLocaleDateString()}</td>
                                            <td style="border: 1px solid #ddd; padding: 8px; text-align: right;">R ${parseFloat(invoice.total_amount).toFixed(2)}</td>
                                            <td style="border: 1px solid #ddd; padding: 8px; text-align: center;">
                                                <span class="status-badge status-${invoice.payment_status}">${invoice.payment_status.charAt(0).toUpperCase() + invoice.payment_status.slice(1)}</span>
                                            </td>
                                        </tr>
                                    `).join('')}
                                </tbody>
                            </table>
                            ${company.invoice_count > 10 ? `<p style="margin-top: 0.5rem; color: #666; font-size: 0.85rem;">Showing last 10 of ${company.invoice_count} invoices</p>` : ''}
                        </div>
                        ` : company.invoice_count > 0 ? `
                        <div style="margin-top: 2rem; padding-top: 1.5rem; border-top: 2px solid #e0e0e0;">
                            <p style="color: #666;">No recent invoices to display.</p>
                        </div>
                        ` : ''}
                    </div>
                `;
                
                modal.style.display = 'flex';
            } else {
                showNotification('Error loading company details: ' + (data.message || 'Unknown error'), 'error');
            }
        })
        .catch(error => {
            console.error('Error loading company details:', error);
            showNotification('Error loading company details. Please try again.', 'error');
        });
}

function closeCompanyDetailsModal() {
    const modal = document.getElementById('company-details-modal');
    if (modal) {
        modal.style.display = 'none';
    }
}

function activateCompany(companyId) {
    if (!confirm('Are you sure you want to activate this company?')) {
        return;
    }
    
    const formData = new URLSearchParams();
    formData.append('action', 'toggle_company_status');
    formData.append('company_id', companyId);
    
    fetch('?page=ajax', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: formData.toString()
    })
    .then(response => response.text())
    .then(text => {
        let data = null;
        try {
            data = JSON.parse(text);
        } catch (e) {
            console.error('Error parsing response:', text);
            showNotification('Error updating company status', 'error');
            return;
        }
        
        if (data.success) {
            showNotification(data.message || 'Company activated successfully!', 'success');
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            showNotification('Error: ' + (data.message || 'Failed to activate company'), 'error');
        }
    })
    .catch(error => {
        console.error('Error activating company:', error);
        showNotification('Error activating company. Please try again.', 'error');
    });
}

function deactivateCompany(companyId) {
    if (!confirm('Are you sure you want to deactivate this company?')) {
        return;
    }
    
    const formData = new URLSearchParams();
    formData.append('action', 'toggle_company_status');
    formData.append('company_id', companyId);
    
    fetch('?page=ajax', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: formData.toString()
    })
    .then(response => response.text())
    .then(text => {
        let data = null;
        try {
            data = JSON.parse(text);
        } catch (e) {
            console.error('Error parsing response:', text);
            showNotification('Error updating company status', 'error');
            return;
        }
        
        if (data.success) {
            showNotification(data.message || 'Company deactivated successfully!', 'success');
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            showNotification('Error: ' + (data.message || 'Failed to deactivate company'), 'error');
        }
    })
    .catch(error => {
        console.error('Error deactivating company:', error);
        showNotification('Error deactivating company. Please try again.', 'error');
    });
}

function deleteCompany(companyId) {
    if (!confirm('Are you sure you want to delete this company? This action cannot be undone.')) {
        return;
    }
    
    const formData = new URLSearchParams();
    formData.append('action', 'delete_company');
    formData.append('company_id', companyId);
    
    fetch('?page=ajax', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: formData.toString()
    })
    .then(response => response.text())
    .then(text => {
        let data = null;
        try {
            data = JSON.parse(text);
        } catch (e) {
            console.error('Error parsing response:', text);
            showNotification('Error deleting company', 'error');
            return;
        }
        
        if (data.success) {
            showNotification(data.message || 'Company deleted successfully!', 'success');
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            showNotification('Error: ' + (data.message || 'Failed to delete company'), 'error');
        }
    })
    .catch(error => {
        console.error('Error deleting company:', error);
        showNotification('Error deleting company. Please try again.', 'error');
    });
}

function toggleInactiveCompanies() {
    const currentUrl = new URL(window.location);
    const showInactive = currentUrl.searchParams.get('show_inactive') === '1';
    currentUrl.searchParams.set('show_inactive', showInactive ? '0' : '1');
    window.location.href = currentUrl.toString();
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function closeCompanyModal() {
    document.getElementById('company-modal').style.display = 'none';
}

// Load Sheet Functions
function createLoadSheetForCompany(companyId, companyName, ratePerPallet, paymentTerms) {
    const modal = document.getElementById('loadsheet-modal');
    const form = document.getElementById('loadsheet-form');
    
    // Reset form
    form.reset();
    document.getElementById('loadsheet-id').value = '';
    
    // Reset contractor fields
    const contractorSelectGroup = document.getElementById('contractor-select-group');
    const contractorCostGroup = document.getElementById('contractor-cost-group');
    if (contractorSelectGroup) contractorSelectGroup.style.display = 'none';
    if (contractorCostGroup) contractorCostGroup.style.display = 'none';
    const contractorSelect = document.getElementById('contractor-select');
    if (contractorSelect) contractorSelect.value = '';
    const contractorCost = document.getElementById('contractor-cost');
    if (contractorCost) contractorCost.value = '';
    
    // Pre-populate company data
    document.getElementById('loadsheet-company-id').value = companyId;
    document.getElementById('loadsheet-company').value = companyName;
    document.getElementById('rate-per-pallet').value = ratePerPallet;
    document.getElementById('loadsheet-date').value = new Date().toISOString().split('T')[0];
    
    // Update modal title
    document.getElementById('loadsheet-modal-title').textContent = `New Load Sheet - ${companyName}`;
    
    // Show modal
    modal.style.display = 'flex';
    
    // Focus on first input
    setTimeout(() => {
        document.getElementById('pallet-quantity').focus();
    }, 100);
}

function closeLoadSheetModal() {
    const modal = document.getElementById('loadsheet-modal');
    if (modal) modal.style.display = 'none';
    
    // Reset contractor fields
    const contractorSelectGroup = document.getElementById('contractor-select-group');
    const contractorCostGroup = document.getElementById('contractor-cost-group');
    if (contractorSelectGroup) contractorSelectGroup.style.display = 'none';
    if (contractorCostGroup) contractorCostGroup.style.display = 'none';
}

function calculateTotal() {
    const quantity = parseFloat(document.getElementById('pallet-quantity').value) || 0;
    const rate = parseFloat(document.getElementById('rate-per-pallet').value) || 0;
    const subtotal = quantity * rate;
    
    document.getElementById('calc-subtotal').textContent = `R ${subtotal.toFixed(2)}`;
    calculateProfit();
}

function calculateProfit() {
    const subtotal = parseFloat(document.getElementById('calc-subtotal').textContent.replace('R ', '')) || 0;
    const contractorCost = parseFloat(document.getElementById('contractor-cost').value) || 0;
    const profit = subtotal - contractorCost;
    
    document.getElementById('calc-contractor-cost').textContent = `R ${contractorCost.toFixed(2)}`;
    document.getElementById('calc-profit').textContent = `R ${profit.toFixed(2)}`;
}

function toggleContractorFields() {
    const deliveryMethod = document.getElementById('delivery-method').value;
    const contractorSelectGroup = document.getElementById('contractor-select-group');
    const contractorCostGroup = document.getElementById('contractor-cost-group');
    const contractorSelect = document.getElementById('contractor-select');
    
    if (deliveryMethod === 'contractor') {
        // Show the contractor dropdown group (will appear in grid layout next to delivery method)
        if (contractorSelectGroup) {
            contractorSelectGroup.style.display = 'block';
        }
        // Load contractors if not already loaded
        if (contractorSelect && contractorSelect.options.length <= 2) {
            loadContractors();
        }
    } else {
        // Hide contractor fields
        if (contractorSelectGroup) {
            contractorSelectGroup.style.display = 'none';
        }
        if (contractorCostGroup) {
            contractorCostGroup.style.display = 'none';
        }
        if (contractorSelect) {
            contractorSelect.value = '';
        }
        const contractorCost = document.getElementById('contractor-cost');
        if (contractorCost) {
            contractorCost.value = '';
        }
        calculateProfit();
    }
}

function handleContractorSelection() {
    const contractorSelect = document.getElementById('contractor-select');
    const contractorCostGroup = document.getElementById('contractor-cost-group');
    const contractorId = contractorSelect.value;
    
    if (contractorId && contractorId !== '') {
        // Show cost field when contractor is selected
        contractorCostGroup.style.display = 'block';
    } else {
        contractorCostGroup.style.display = 'none';
        document.getElementById('contractor-cost').value = '';
        calculateProfit();
    }
}

function handleLoadSheetSubmit(event) {
    event.preventDefault();
    
    const form = event.target;
    const deliveryMethod = document.getElementById('delivery-method').value;
    const contractorSelect = document.getElementById('contractor-select');
    const loadSheetId = document.getElementById('loadsheet-id').value;
    
    // Validate contractor selection if delivery method is contractor
    if (deliveryMethod === 'contractor') {
        const contractorId = contractorSelect ? contractorSelect.value : '';
        if (!contractorId || contractorId === '') {
            showNotification('Please select a contractor', 'error');
            return;
        }
    }
    
    const formData = new FormData(form);
    const submitButton = form.querySelector('button[type="submit"]');
    const originalText = submitButton.textContent;
    
    submitButton.disabled = true;
    submitButton.textContent = loadSheetId ? 'Updating...' : 'Saving...';
    
    // Convert FormData to URL-encoded string
    const data = new URLSearchParams();
    for (let [key, value] of formData.entries()) {
        data.append(key, value);
    }
    data.append('action', 'create_loadsheet');
    if (loadSheetId) {
        data.append('loadsheet_id', loadSheetId);
    }
    
    console.log(loadSheetId ? 'Updating load sheet:' : 'Creating load sheet:', Object.fromEntries(data));
    
    fetch('?page=ajax', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: data.toString()
    })
    .then(response => response.json())
    .then(data => {
        console.log('Load sheet response:', data);
        
        if (data.success) {
            showNotification(loadSheetId ? 'Load sheet updated successfully!' : 'Load sheet created successfully!', 'success');
            closeLoadSheetModal();
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            showNotification('Error saving load sheet: ' + data.message, 'error');
            submitButton.disabled = false;
            submitButton.textContent = originalText;
        }
    })
    .catch(error => {
        console.error('Error saving load sheet:', error);
        showNotification('Error saving load sheet. Please try again.', 'error');
        submitButton.disabled = false;
        submitButton.textContent = originalText;
    });
}

function handleCompanySubmit(event) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    const submitButton = form.querySelector('button[type="submit"]');
    const originalText = submitButton.textContent;
    
    submitButton.disabled = true;
    submitButton.textContent = 'Saving...';
    
    // Convert FormData to URL-encoded string
    const data = new URLSearchParams();
    for (let [key, value] of formData.entries()) {
        data.append(key, value);
    }
    data.append('action', 'save_company');
    
    console.log('Saving company:', Object.fromEntries(data));
    
    fetch('?page=ajax', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: data.toString()
    })
    .then(response => response.text())
    .then(text => {
        let dataObj = null;
        try {
            dataObj = JSON.parse(text);
        } catch (e) {
            console.error('Company save returned non-JSON:', text);
            throw new Error('Invalid JSON');
        }
        console.log('Company save response:', dataObj);
        
        if (dataObj.success) {
            showNotification('Company saved successfully!', 'success');
            closeCompanyModal();
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            showNotification('Error saving company: ' + (dataObj.message || 'Unknown error'), 'error');
            submitButton.disabled = false;
            submitButton.textContent = originalText;
        }
    })
    .catch(error => {
        console.error('Error saving company:', error);
        showNotification('Error saving company. Please try again.', 'error');
        submitButton.disabled = false;
        submitButton.textContent = originalText;
    });
}

function refreshStats() {
    // This would refresh dashboard stats without page reload
    console.log('Refreshing dashboard stats...');
}

function showNotification(message, type = 'info') {
    // Remove existing notifications
    document.querySelectorAll('.notification').forEach(notification => {
        notification.remove();
    });
    
    // Create new notification
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        notification.remove();
    }, 5000);
}

// Close modal when clicking outside
document.addEventListener('click', function(event) {
    const emailModal = document.getElementById('email-dialog');
    if (event.target === emailModal) {
        closeEmailDialog();
        currentInvoiceData = null;
        currentStatementData = null;
    }
    const loadsheetModal = document.getElementById('loadsheet-modal');
    if (event.target === loadsheetModal) {
        closeLoadSheetModal();
    }
    const companyDetailsModal = document.getElementById('company-details-modal');
    if (event.target === companyDetailsModal) {
        closeCompanyDetailsModal();
    }
    const companyModal = document.getElementById('company-modal');
    if (event.target === companyModal) {
        closeCompanyModal();
    }
    const invoiceDetailsModal = document.getElementById('invoice-details-modal');
    if (event.target === invoiceDetailsModal) {
        closeInvoiceDetailsModal();
    }
    const statementDetailsModal = document.getElementById('statement-details-modal');
    if (event.target === statementDetailsModal) {
        closeStatementDetailsModal();
    }
    const generateStatementModal = document.getElementById('generate-statement-modal');
    if (event.target === generateStatementModal) {
        closeGenerateStatementModal();
    }
    const statementModal = document.getElementById('statement-modal');
    if (event.target === statementModal) {
        closeStatementModal();
    }
});

// Contractor Management Functions
function loadContractors() {
    const contractorSelect = document.getElementById('contractor-select');
    if (!contractorSelect) return;
    
    fetch('?page=ajax&action=get_contractors')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.contractors) {
                populateContractorDropdown(data.contractors);
            }
        })
        .catch(error => {
            console.error('Error loading contractors:', error);
        });
}

function populateContractorDropdown(contractors) {
    const contractorSelect = document.getElementById('contractor-select');
    if (!contractorSelect) return;
    
    // Clear existing contractors
    contractorSelect.innerHTML = '';
    
    // Add Select option
    const selectOption = document.createElement('option');
    selectOption.value = '';
    selectOption.textContent = 'Select Contractor';
    contractorSelect.appendChild(selectOption);
    
    // Add contractor options (only existing contractors)
    contractors.forEach(contractor => {
        const option = document.createElement('option');
        option.value = contractor.id;
        option.textContent = contractor.name;
        contractorSelect.appendChild(option);
    });
}

function showAddContractorModal() {
    const modal = document.getElementById('contractor-modal');
    const form = document.getElementById('contractor-form');
    
    if (!modal || !form) return;
    
    // Reset form
    form.reset();
    modal.style.display = 'flex';
    
    // Focus on first input
    setTimeout(() => {
        const nameInput = document.getElementById('contractor-name');
        if (nameInput) nameInput.focus();
    }, 100);
}

function closeContractorModal() {
    const modal = document.getElementById('contractor-modal');
    if (modal) {
        modal.style.display = 'none';
    }
}

function handleContractorSubmit(event) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    const submitButton = form.querySelector('button[type="submit"]');
    const originalText = submitButton.textContent;
    
    submitButton.disabled = true;
    submitButton.textContent = 'Adding...';
    
    // Convert FormData to URL-encoded string
    const data = new URLSearchParams();
    for (let [key, value] of formData.entries()) {
        data.append(key, value);
    }
    data.append('action', 'create_contractor');
    
    console.log('Creating contractor:', Object.fromEntries(data));
    
    fetch('?page=ajax', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: data.toString()
    })
    .then(response => response.json())
    .then(data => {
        console.log('Contractor creation response:', data);
        
        if (data.success && data.contractor) {
            showNotification('Contractor added successfully!', 'success');
            
            // Reload contractors to include new one
            loadContractors();
            
            // Select the new contractor
            const contractorSelect = document.getElementById('contractor-select');
            if (contractorSelect) {
                // Wait for dropdown to be populated
                setTimeout(() => {
                    contractorSelect.value = data.contractor.id;
                    handleContractorSelection();
                }, 200);
            }
            
            closeContractorModal();
        } else {
            showNotification('Error adding contractor: ' + (data.message || 'Unknown error'), 'error');
            submitButton.disabled = false;
            submitButton.textContent = originalText;
        }
    })
    .catch(error => {
        console.error('Error creating contractor:', error);
        showNotification('Error adding contractor. Please try again.', 'error');
        submitButton.disabled = false;
        submitButton.textContent = originalText;
    });
}

// Invoice Functions
function viewInvoice(invoiceId) {
    fetch('?page=ajax&action=get_invoice_details&invoice_id=' + invoiceId)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.invoice) {
                const invoice = data.invoice;
                const modal = document.getElementById('invoice-details-modal');
                const content = document.getElementById('invoice-details-content');
                
                content.innerHTML = `
                    <div style="line-height: 1.8; word-wrap: break-word; overflow-wrap: break-word;">
                        <div style="margin-bottom: 1rem;">
                            <strong>Invoice Number:</strong><br>
                            <span style="word-break: break-word;">${escapeHtml(invoice.invoice_number)}</span>
                        </div>
                        <div style="margin-bottom: 1rem;">
                            <strong>Company:</strong><br>
                            <span style="word-break: break-word;">${escapeHtml(invoice.company_name)}</span>
                        </div>
                        <div style="margin-bottom: 1rem;">
                            <strong>Invoice Date:</strong><br>
                            ${new Date(invoice.invoice_date).toLocaleDateString()}
                        </div>
                        <div style="margin-bottom: 1rem;">
                            <strong>Due Date:</strong><br>
                            ${new Date(invoice.due_date).toLocaleDateString()}
                        </div>
                        <div style="margin-bottom: 1rem;">
                            <strong>Description:</strong><br>
                            <span style="word-break: break-word;">${escapeHtml(invoice.cargo_description || '-')}</span>
                        </div>
                        <div style="margin-bottom: 1rem;">
                            <strong>Pallets:</strong><br>
                            ${invoice.pallet_quantity}
                        </div>
                        <div style="margin-bottom: 1rem;">
                            <strong>Subtotal:</strong><br>
                            R ${parseFloat(invoice.subtotal).toFixed(2)}
                        </div>
                        <div style="margin-bottom: 1rem;">
                            <strong>VAT (${invoice.vat_rate}%):</strong><br>
                            R ${parseFloat(invoice.vat_amount).toFixed(2)}
                        </div>
                        <div style="margin-bottom: 1rem;">
                            <strong>Total Amount:</strong><br>
                            R ${parseFloat(invoice.total_amount).toFixed(2)}
                        </div>
                        <div style="margin-bottom: 1rem;">
                            <strong>Payment Status:</strong><br>
                            <span class="status-badge status-${invoice.payment_status}">${invoice.payment_status.charAt(0).toUpperCase() + invoice.payment_status.slice(1)}</span>
                        </div>
                    </div>
                `;
                
                modal.style.display = 'flex';
            } else {
                showNotification('Error loading invoice: ' + (data.message || 'Unknown error'), 'error');
            }
        })
        .catch(error => {
            console.error('Error loading invoice:', error);
            showNotification('Error loading invoice. Please try again.', 'error');
        });
}

function closeInvoiceDetailsModal() {
    const modal = document.getElementById('invoice-details-modal');
    if (modal) {
        modal.style.display = 'none';
    }
}

function downloadInvoice(invoiceId) {
    window.location.href = '?page=ajax&action=download_invoice&invoice_id=' + invoiceId;
}

// Load Sheet Functions
function showAddLoadSheetForm() {
    const modal = document.getElementById('loadsheet-modal');
    const form = document.getElementById('loadsheet-form');
    
    if (!modal || !form) return;
    
    // Reset form
    form.reset();
    document.getElementById('loadsheet-id').value = '';
    document.getElementById('loadsheet-company-id').value = '';
    
    // Reset contractor fields
    const contractorSelectGroup = document.getElementById('contractor-select-group');
    const contractorCostGroup = document.getElementById('contractor-cost-group');
    if (contractorSelectGroup) contractorSelectGroup.style.display = 'none';
    if (contractorCostGroup) contractorCostGroup.style.display = 'none';
    
    // Reset calculations
    document.getElementById('calc-subtotal').textContent = 'R 0.00';
    document.getElementById('calc-contractor-cost').textContent = 'R 0.00';
    document.getElementById('calc-profit').textContent = 'R 0.00';
    
    // Set default date
    document.getElementById('loadsheet-date').value = new Date().toISOString().split('T')[0];
    
    // Update modal title
    const title = document.getElementById('loadsheet-modal-title');
    if (title) title.textContent = 'New Load Sheet';
    
    modal.style.display = 'flex';
    
    // Focus on first input
    setTimeout(() => {
        const companySelect = document.getElementById('loadsheet-company');
        if (companySelect) companySelect.focus();
    }, 100);
}

function viewLoadSheet(loadSheetId) {
    fetch('?page=ajax&action=get_loadsheet_details&loadsheet_id=' + loadSheetId)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.loadsheet) {
                const ls = data.loadsheet;
                showNotification(`Load Sheet Details: ${ls.company_name} - ${ls.pallet_quantity} pallets - R ${parseFloat(ls.final_rate).toFixed(2)}`, 'info');
                // Could show in a modal if needed
            } else {
                showNotification('Error loading load sheet: ' + (data.message || 'Unknown error'), 'error');
            }
        })
        .catch(error => {
            console.error('Error loading load sheet:', error);
            showNotification('Error loading load sheet. Please try again.', 'error');
        });
}

function editLoadSheet(loadSheetId) {
    fetch('?page=ajax&action=get_loadsheet_details&loadsheet_id=' + loadSheetId)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.loadsheet) {
                const ls = data.loadsheet;
                const modal = document.getElementById('loadsheet-modal');
                const form = document.getElementById('loadsheet-form');
                
                if (!modal || !form) return;
                
                // Populate form
                document.getElementById('loadsheet-id').value = ls.id;
                document.getElementById('loadsheet-company-id').value = ls.company_id;
                document.getElementById('loadsheet-company').value = ls.company_name;
                document.getElementById('loadsheet-date').value = ls.requested_date || ls.created_at.split(' ')[0];
                document.getElementById('pallet-quantity').value = ls.pallet_quantity;
                document.getElementById('rate-per-pallet').value = ls.rate_per_pallet;
                document.getElementById('cargo-description').value = ls.cargo_description || '';
                document.getElementById('loadsheet-status').value = ls.status;
                
                // Handle delivery method
                const deliveryMethod = ls.delivery_method === 'own' ? 'own_driver' : 'contractor';
                document.getElementById('delivery-method').value = deliveryMethod;
                toggleContractorFields();
                
                if (deliveryMethod === 'contractor' && ls.contractor_name) {
                    // Load contractors and select the one used
                    loadContractors();
                    setTimeout(() => {
                        const contractorSelect = document.getElementById('contractor-select');
                        if (contractorSelect) {
                            // Find contractor by name
                            for (let option of contractorSelect.options) {
                                if (option.text === ls.contractor_name) {
                                    contractorSelect.value = option.value;
                                    handleContractorSelection();
                                    break;
                                }
                            }
                        }
                        document.getElementById('contractor-cost').value = ls.contractor_cost || '';
                    }, 300);
                }
                
                // Update calculations
                calculateTotal();
                
                // Update modal title
                const title = document.getElementById('loadsheet-modal-title');
                if (title) title.textContent = `Edit Load Sheet - ${ls.company_name}`;
                
                modal.style.display = 'flex';
            } else {
                showNotification('Error loading load sheet: ' + (data.message || 'Unknown error'), 'error');
            }
        })
        .catch(error => {
            console.error('Error loading load sheet:', error);
            showNotification('Error loading load sheet. Please try again.', 'error');
        });
}

function loadCompanyDetails() {
    const companySelect = document.getElementById('loadsheet-company');
    if (!companySelect) return;
    
    const companyId = companySelect.value;
    if (!companyId) return;
    
    const selectedOption = companySelect.options[companySelect.selectedIndex];
    const rate = selectedOption.getAttribute('data-rate');
    const paymentTerms = selectedOption.getAttribute('data-payment-terms');
    
    if (rate) {
        document.getElementById('rate-per-pallet').value = rate;
        calculateTotal();
    }
}

// Statement Functions
function showGenerateStatementForm() {
    const modal = document.getElementById('generate-statement-modal');
    if (!modal) return;
    
    const form = document.getElementById('generate-statement-form');
    if (form) {
        form.reset();
        document.getElementById('statement-month').value = new Date().toISOString().slice(0, 7);
    }
    
    modal.style.display = 'flex';
}

function closeGenerateStatementModal() {
    const modal = document.getElementById('generate-statement-modal');
    if (modal) {
        modal.style.display = 'none';
    }
}

function handleGenerateStatementSubmit(event) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    const submitButton = form.querySelector('button[type="submit"]');
    const originalText = submitButton.textContent;
    
    submitButton.disabled = true;
    submitButton.textContent = 'Generating...';
    
    const data = new URLSearchParams();
    for (let [key, value] of formData.entries()) {
        data.append(key, value);
    }
    data.append('action', 'generate_statement');
    
    fetch('?page=ajax', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: data.toString()
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(`Statement ${data.statement_number} generated successfully!`, 'success');
            // Close both modals
            closeStatementModal();
            closeGenerateStatementModal();
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            showNotification('Error generating statement: ' + (data.message || 'Unknown error'), 'error');
            submitButton.disabled = false;
            submitButton.textContent = originalText;
        }
    })
    .catch(error => {
        console.error('Error generating statement:', error);
        showNotification('Error generating statement. Please try again.', 'error');
        submitButton.disabled = false;
        submitButton.textContent = originalText;
    });
}

function generateMonthlyStatement() {
    // Check if statement-modal exists (invoices page)
    const statementModal = document.getElementById('statement-modal');
    if (statementModal) {
        statementModal.style.display = 'flex';
        const form = document.getElementById('statement-form');
        if (form) {
            form.reset();
            const monthInput = document.getElementById('statement-month');
            if (monthInput) {
                monthInput.value = new Date().toISOString().slice(0, 7);
            }
        }
    } else {
        // Fall back to generate-statement-modal (statements page)
        showGenerateStatementForm();
    }
}

function generateStatementFromInvoice(invoiceId, companyId, month) {
    // Open the statement modal and pre-populate with company and month
    const statementModal = document.getElementById('statement-modal');
    if (statementModal) {
        statementModal.style.display = 'flex';
        const form = document.getElementById('statement-form');
        if (form) {
            // Pre-populate company
            const companySelect = document.getElementById('statement-company');
            if (companySelect) {
                companySelect.value = companyId;
            }
            // Pre-populate month
            const monthInput = document.getElementById('statement-month');
            if (monthInput) {
                monthInput.value = month;
            }
        }
    } else {
        // Fall back to generate-statement-modal (statements page)
        const generateModal = document.getElementById('generate-statement-modal');
        if (generateModal) {
            generateModal.style.display = 'flex';
            const form = document.getElementById('generate-statement-form');
            if (form) {
                const companySelect = document.getElementById('statement-company');
                if (companySelect) {
                    companySelect.value = companyId;
                }
                const monthInput = document.getElementById('statement-month');
                if (monthInput) {
                    monthInput.value = month;
                }
            }
        }
    }
}

function viewStatement(statementId) {
    fetch('?page=ajax&action=get_statement_details&statement_id=' + statementId)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.statement) {
                const stmt = data.statement;
                const modal = document.getElementById('statement-details-modal');
                const content = document.getElementById('statement-details-content');
                
                if (!modal || !content) return;
                
                const statementNumber = 'STMT' + new Date(stmt.statement_date).getFullYear() + 
                    String(new Date(stmt.statement_date).getMonth() + 1).padStart(2, '0') + 
                    String(stmt.id).padStart(3, '0');
                const period = new Date(stmt.statement_period + '-01').toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
                
                let itemsHtml = '';
                if (stmt.items && stmt.items.length > 0) {
                    itemsHtml = '<table style="width: 100%; border-collapse: collapse; margin: 20px 0;"><thead><tr><th style="border: 1px solid #ddd; padding: 8px;">Invoice #</th><th style="border: 1px solid #ddd; padding: 8px;">Date</th><th style="border: 1px solid #ddd; padding: 8px;">Amount</th><th style="border: 1px solid #ddd; padding: 8px;">Status</th></tr></thead><tbody>';
                    stmt.items.forEach(item => {
                        itemsHtml += `<tr><td style="border: 1px solid #ddd; padding: 8px;">${escapeHtml(item.invoice_number)}</td><td style="border: 1px solid #ddd; padding: 8px;">${new Date(item.invoice_date).toLocaleDateString()}</td><td style="border: 1px solid #ddd; padding: 8px;">R ${parseFloat(item.amount).toFixed(2)}</td><td style="border: 1px solid #ddd; padding: 8px;">${item.payment_status}</td></tr>`;
                    });
                    itemsHtml += '</tbody></table>';
                }
                
                content.innerHTML = `
                    <div style="line-height: 1.8;">
                        <div style="margin-bottom: 1rem;">
                            <strong>Statement Number:</strong><br>
                            ${statementNumber}
                        </div>
                        <div style="margin-bottom: 1rem;">
                            <strong>Company:</strong><br>
                            ${escapeHtml(stmt.company_name)}
                        </div>
                        <div style="margin-bottom: 1rem;">
                            <strong>Period:</strong><br>
                            ${period}
                        </div>
                        <div style="margin-bottom: 1rem;">
                            <strong>Opening Balance:</strong><br>
                            R ${parseFloat(stmt.opening_balance).toFixed(2)}
                        </div>
                        <div style="margin-bottom: 1rem;">
                            <strong>Total Charges:</strong><br>
                            R ${parseFloat(stmt.total_charges).toFixed(2)}
                        </div>
                        <div style="margin-bottom: 1rem;">
                            <strong>Total Payments:</strong><br>
                            R ${parseFloat(stmt.total_payments).toFixed(2)}
                        </div>
                        <div style="margin-bottom: 1rem;">
                            <strong>Closing Balance:</strong><br>
                            R ${parseFloat(stmt.closing_balance).toFixed(2)}
                        </div>
                        ${itemsHtml}
                    </div>
                `;
                
                modal.style.display = 'flex';
            } else {
                showNotification('Error loading statement: ' + (data.message || 'Unknown error'), 'error');
            }
        })
        .catch(error => {
            console.error('Error loading statement:', error);
            showNotification('Error loading statement. Please try again.', 'error');
        });
}

function closeStatementDetailsModal() {
    const modal = document.getElementById('statement-details-modal');
    if (modal) {
        modal.style.display = 'none';
    }
}

function downloadStatement(statementId) {
    window.location.href = '?page=ajax&action=download_statement&statement_id=' + statementId;
}

let currentStatementData = null;

function sendStatementEmail(statementId) {
    if (!statementId) {
        // Called from dialog button, use currentStatementData
        sendStatementEmailFromDialog();
        return;
    }
    
    fetch('?page=ajax&action=get_statement_details&statement_id=' + statementId)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.statement) {
                currentStatementData = { statement_id: statementId };
                const stmt = data.statement;
                const dialog = document.getElementById('email-dialog');
                const detailsElement = document.getElementById('statement-details') || document.getElementById('invoice-details');
                
                if (dialog && detailsElement) {
                    const statementNumber = 'STMT' + new Date(stmt.statement_date).getFullYear() + 
                        String(new Date(stmt.statement_date).getMonth() + 1).padStart(2, '0') + 
                        String(stmt.id).padStart(3, '0');
                    
                    detailsElement.innerHTML = `
                        <p><strong>Statement ${statementNumber}</strong></p>
                        <p>Company: ${escapeHtml(stmt.company_name)}</p>
                        <p>Closing Balance: R ${parseFloat(stmt.closing_balance).toFixed(2)}</p>
                    `;
                    
                    // Update dialog title if it exists
                    const title = dialog.querySelector('h3');
                    if (title) title.textContent = 'Send Statement to Customer';
                    
                    // Update send button onclick
                    const sendButton = dialog.querySelector('.btn-primary');
                    if (sendButton) {
                        sendButton.setAttribute('onclick', 'sendStatementEmail()');
                    }
                    
                    dialog.style.display = 'flex';
                    
                    setTimeout(() => {
                        const emailInput = document.getElementById('email-address');
                        if (emailInput) emailInput.focus();
                    }, 100);
                }
            } else {
                showNotification('Error loading statement: ' + (data.message || 'Unknown error'), 'error');
            }
        })
        .catch(error => {
            console.error('Error loading statement:', error);
            showNotification('Error loading statement. Please try again.', 'error');
        });
}

function sendStatementEmailFromDialog() {
    const emailAddress = document.getElementById('email-address').value.trim();
    
    if (!emailAddress) {
        alert('Please enter an email address');
        return;
    }
    
    if (!currentStatementData) {
        alert('No statement data available');
        return;
    }
    
    const sendButton = document.querySelector('#email-dialog .btn-primary');
    const originalText = sendButton.textContent;
    sendButton.disabled = true;
    sendButton.textContent = 'Sending...';
    
    fetch('?page=ajax', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=send_statement_email&statement_id=${currentStatementData.statement_id}&email_address=${encodeURIComponent(emailAddress)}`
    })
    .then(response => {
        // Check if response is ok
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        // Try to parse as JSON
        return response.text().then(text => {
            try {
                return JSON.parse(text);
            } catch (e) {
                console.error('Response was not JSON:', text);
                throw new Error('Invalid response from server');
            }
        });
    })
    .then(data => {
        if (data.success) {
            showNotification(`Statement sent successfully to ${emailAddress}`, 'success');
            closeEmailDialog();
            currentStatementData = null;
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            showNotification('Error sending statement: ' + (data.message || 'Unknown error'), 'error');
            sendButton.disabled = false;
            sendButton.textContent = originalText;
        }
    })
    .catch(error => {
        console.error('Error sending statement email:', error);
        showNotification('Error sending statement: ' + error.message + '. Please check your email configuration.', 'error');
        sendButton.disabled = false;
        sendButton.textContent = originalText;
    });
}

function closeStatementModal() {
    // Close statement-modal (invoices page)
    const statementModal = document.getElementById('statement-modal');
    if (statementModal) {
        statementModal.style.display = 'none';
    }
    // Also close generate-statement-modal (statements page) if it exists
    closeGenerateStatementModal();
}

// Export Functions
function exportInvoices() {
    showNotification('Export functionality coming soon!', 'info');
}

function filterInvoices() {
    const statusFilter = document.getElementById('status-filter');
    const dateFilter = document.getElementById('date-filter');
    const invoiceSearch = document.getElementById('invoice-search');
    const invoiceRows = document.querySelectorAll('.invoice-row');
    
    if (!invoiceRows.length) return;
    
    const statusValue = statusFilter ? statusFilter.value : '';
    const dateValue = dateFilter ? dateFilter.value : '';
    const searchValue = invoiceSearch ? invoiceSearch.value.toLowerCase().trim() : '';
    
    // Calculate date range if needed
    let dateStart = null;
    let dateEnd = null;
    
    if (dateValue) {
        const now = new Date();
        switch (dateValue) {
            case 'this_month':
                dateStart = new Date(now.getFullYear(), now.getMonth(), 1);
                dateEnd = new Date(now.getFullYear(), now.getMonth() + 1, 0);
                break;
            case 'last_month':
                dateStart = new Date(now.getFullYear(), now.getMonth() - 1, 1);
                dateEnd = new Date(now.getFullYear(), now.getMonth(), 0);
                break;
            case 'this_quarter':
                const quarter = Math.floor(now.getMonth() / 3);
                dateStart = new Date(now.getFullYear(), quarter * 3, 1);
                dateEnd = new Date(now.getFullYear(), (quarter + 1) * 3, 0);
                break;
            case 'this_year':
                dateStart = new Date(now.getFullYear(), 0, 1);
                dateEnd = new Date(now.getFullYear(), 11, 31);
                break;
        }
    }
    
    let visibleCount = 0;
    let pendingTotal = 0;
    let paidTotal = 0;
    
    invoiceRows.forEach(row => {
        const rowStatus = row.getAttribute('data-status') || '';
        const rowDate = row.getAttribute('data-invoice-date');
        const invoiceNumber = row.getAttribute('data-invoice-number') || '';
        const company = row.getAttribute('data-company') || '';
        const description = row.getAttribute('data-description') || '';
        
        // Status filter
        let statusMatch = !statusValue || rowStatus === statusValue;
        
        // Date filter
        let dateMatch = true;
        if (dateValue && dateStart && dateEnd && rowDate) {
            const invoiceDate = new Date(rowDate);
            dateMatch = invoiceDate >= dateStart && invoiceDate <= dateEnd;
        }
        
        // Search filter
        let searchMatch = true;
        if (searchValue) {
            searchMatch = invoiceNumber.includes(searchValue) || 
                         company.includes(searchValue) || 
                         description.includes(searchValue);
        }
        
        // Show/hide row based on all filters
        if (statusMatch && dateMatch && searchMatch) {
            row.style.display = '';
            visibleCount++;
            
            // Calculate totals for visible rows
            const totalCell = row.querySelector('td:nth-child(8) strong');
            if (totalCell) {
                const totalText = totalCell.textContent.replace(/[R\s,]/g, '');
                const total = parseFloat(totalText) || 0;
                
                if (rowStatus === 'pending' || rowStatus === 'overdue') {
                    pendingTotal += total;
                } else if (rowStatus === 'paid') {
                    paidTotal += total;
                }
            }
        } else {
            row.style.display = 'none';
        }
    });
    
    // Update summary cards
    updateInvoiceSummary(visibleCount, pendingTotal, paidTotal);
    
    // Show "no results" message if needed
    const tbody = document.querySelector('.invoices-table tbody');
    let noDataRow = tbody.querySelector('.no-results-row');
    
    if (visibleCount === 0 && invoiceRows.length > 0) {
        if (!noDataRow) {
            noDataRow = document.createElement('tr');
            noDataRow.className = 'no-results-row';
            noDataRow.innerHTML = '<td colspan="11" class="no-data">No invoices match the current filters.</td>';
            tbody.appendChild(noDataRow);
        }
        noDataRow.style.display = '';
    } else if (noDataRow) {
        noDataRow.style.display = 'none';
    }
}

function updateInvoiceSummary(totalCount, pendingTotal, paidTotal) {
    const totalInvoicesCard = document.querySelector('.summary-card:first-child .summary-number');
    const pendingAmountCard = document.querySelector('.summary-card:nth-child(2) .summary-number');
    const paidAmountCard = document.querySelector('.summary-card:nth-child(3) .summary-number');
    
    if (totalInvoicesCard) {
        totalInvoicesCard.textContent = totalCount;
    }
    if (pendingAmountCard) {
        pendingAmountCard.textContent = 'R ' + pendingTotal.toLocaleString('en-ZA', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }
    if (paidAmountCard) {
        paidAmountCard.textContent = 'R ' + paidTotal.toLocaleString('en-ZA', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }
}

function exportStatements() {
    showNotification('Export functionality coming soon!', 'info');
}

// Handle Enter key in email input
document.addEventListener('keydown', function(event) {
    if (event.key === 'Enter' && event.target.id === 'email-address') {
        if (currentInvoiceData) {
            sendInvoiceEmail();
        } else if (currentStatementData) {
            sendStatementEmailFromDialog();
        }
    }
    
    if (event.key === 'Escape') {
        closeEmailDialog();
        closeLoadSheetModal();
        closeContractorModal();
        closeCompanyDetailsModal();
        closeCompanyModal();
        closeInvoiceDetailsModal();
        closeStatementDetailsModal();
        closeGenerateStatementModal();
        closeStatementModal();
    }
});
