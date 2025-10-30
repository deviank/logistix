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
    const detailsElement = document.getElementById('invoice-details');
    
    detailsElement.innerHTML = `
        <p><strong>Invoice ${invoiceData.invoice_number}</strong> has been created successfully!</p>
        <p>Total Amount: R ${parseFloat(invoiceData.total_amount).toFixed(2)}</p>
    `;
    
    dialog.style.display = 'flex';
    
    // Focus on email input
    setTimeout(() => {
        document.getElementById('email-address').focus();
    }, 100);
}

function closeEmailDialog() {
    const dialog = document.getElementById('email-dialog');
    dialog.style.display = 'none';
    currentInvoiceData = null;
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

function createSampleData() {
    if (!confirm('This will create sample data for testing. Continue?')) {
        return;
    }
    
    showNotification('Sample data already exists in the database!', 'info');
}

// Company Management Functions
function showAddCompanyForm() {
    const modal = document.getElementById('company-modal');
    const form = document.getElementById('company-form');
    const title = document.getElementById('modal-title');
    
    // Reset form
    form.reset();
    document.getElementById('company-id').value = '';
    title.textContent = 'Add New Company';
    
    modal.style.display = 'flex';
}

function editCompany(companyId) {
    // This would load company data and show edit form
    showNotification('Edit company functionality coming soon!', 'info');
}

function viewCompanyDetails(companyId) {
    // This would show company details
    showNotification('Company details functionality coming soon!', 'info');
}

function activateCompany(companyId) {
    if (!confirm('Are you sure you want to activate this company?')) {
        return;
    }
    showNotification('Company activation functionality coming soon!', 'info');
}

function deactivateCompany(companyId) {
    if (!confirm('Are you sure you want to deactivate this company?')) {
        return;
    }
    showNotification('Company deactivation functionality coming soon!', 'info');
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
    submitButton.textContent = 'Saving...';
    
    // Convert FormData to URL-encoded string
    const data = new URLSearchParams();
    for (let [key, value] of formData.entries()) {
        data.append(key, value);
    }
    data.append('action', 'create_loadsheet');
    
    console.log('Creating load sheet:', Object.fromEntries(data));
    
    fetch('?page=ajax', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: data.toString()
    })
    .then(response => response.json())
    .then(data => {
        console.log('Load sheet creation response:', data);
        
        if (data.success) {
            showNotification('Load sheet created successfully!', 'success');
            closeLoadSheetModal();
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            showNotification('Error creating load sheet: ' + data.message, 'error');
            submitButton.disabled = false;
            submitButton.textContent = originalText;
        }
    })
    .catch(error => {
        console.error('Error creating load sheet:', error);
        showNotification('Error creating load sheet. Please try again.', 'error');
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
    const modal = document.getElementById('email-dialog');
    if (event.target === modal) {
        closeEmailDialog();
    }
    const loadsheetModal = document.getElementById('loadsheet-modal');
    if (event.target === loadsheetModal) {
        closeLoadSheetModal();
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

// Handle Enter key in email input
document.addEventListener('keydown', function(event) {
    if (event.key === 'Enter' && event.target.id === 'email-address') {
        sendInvoiceEmail();
    }
    
    if (event.key === 'Escape') {
        closeEmailDialog();
        closeLoadSheetModal();
        closeContractorModal();
    }
});
