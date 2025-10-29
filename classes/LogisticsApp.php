<?php
/**
 * Main Application Class
 */

class LogisticsApp {
    private $db;
    
    public function __construct($database) {
        $this->db = $database;
    }
    
    public function showDashboard() {
        $stats = $this->getDashboardStats();
        $recentLoadSheets = $this->getRecentLoadSheets();
        $pendingInvoices = $this->getPendingInvoices();
        $companies = $this->getActiveCompanies();
        
        include TEMPLATES_PATH . 'dashboard.php';
    }
    
    public function showCompanies() {
        $companies = $this->db->fetchAll("SELECT * FROM companies ORDER BY name");
        include TEMPLATES_PATH . 'companies.php';
    }
    
    public function showLoadSheets() {
        $loadSheets = $this->db->fetchAll("
            SELECT ls.*, c.name as company_name 
            FROM load_sheets ls 
            JOIN companies c ON ls.company_id = c.id 
            ORDER BY ls.created_at DESC
        ");
        include TEMPLATES_PATH . 'loadsheets.php';
    }
    
    public function showInvoices() {
        $invoices = $this->db->fetchAll("
            SELECT i.*, c.name as company_name, ls.pallet_quantity, ls.cargo_description
            FROM invoices i 
            JOIN companies c ON i.company_id = c.id 
            JOIN load_sheets ls ON i.load_sheet_id = ls.id
            ORDER BY i.created_at DESC
        ");
        include TEMPLATES_PATH . 'invoices.php';
    }
    
    public function showStatements() {
        $statements = $this->db->fetchAll("
            SELECT s.*, c.name as company_name 
            FROM statements s 
            JOIN companies c ON s.company_id = c.id 
            ORDER BY s.created_at DESC
        ");
        include TEMPLATES_PATH . 'statements.php';
    }
    
    public function handleAjax() {
        $action = $_POST['action'] ?? $_GET['action'] ?? '';
        
        switch ($action) {
            case 'create_invoice':
                $this->createInvoice();
                break;
            case 'mark_invoice_paid':
                $this->markInvoicePaid();
                break;
            case 'send_invoice_email':
                $this->sendInvoiceEmail();
                break;
            case 'get_company_details':
                $this->getCompanyDetails();
                break;
            case 'create_sample_data':
                $this->createSampleData();
                break;
            case 'create_loadsheet':
                $this->createLoadSheet();
                break;
            case 'save_company':
                $this->saveCompany();
                break;
            case 'get_contractors':
                $this->getContractors();
                break;
            case 'create_contractor':
                $this->createContractor();
                break;
            default:
                http_response_code(404);
                echo json_encode(['error' => 'Action not found']);
                break;
        }
    }
    
    private function getDashboardStats() {
        $stats = [];
        
        // Invoices this month
        $stats['invoices_this_month'] = $this->db->fetchOne("
            SELECT COUNT(*) as count 
            FROM invoices 
            WHERE MONTH(invoice_date) = MONTH(CURRENT_DATE()) 
            AND YEAR(invoice_date) = YEAR(CURRENT_DATE())
        ")['count'];
        
        // Outstanding balance
        $stats['outstanding_balance'] = $this->db->fetchOne("
            SELECT COALESCE(SUM(total_amount), 0) as balance 
            FROM invoices 
            WHERE payment_status = 'pending'
        ")['balance'];
        
        // Active companies
        $stats['active_companies'] = $this->db->fetchOne("
            SELECT COUNT(*) as count 
            FROM companies 
            WHERE status = 'active'
        ")['count'];
        
        return $stats;
    }
    
    private function getRecentLoadSheets() {
        return $this->db->fetchAll("
            SELECT ls.*, c.name as company_name 
            FROM load_sheets ls 
            JOIN companies c ON ls.company_id = c.id 
            ORDER BY ls.created_at DESC 
            LIMIT 5
        ");
    }
    
    private function getPendingInvoices() {
        return $this->db->fetchAll("
            SELECT i.*, c.name as company_name 
            FROM invoices i 
            JOIN companies c ON i.company_id = c.id 
            WHERE i.payment_status = 'pending'
            ORDER BY i.due_date ASC 
            LIMIT 5
        ");
    }
    
    private function getActiveCompanies() {
        return $this->db->fetchAll("
            SELECT * FROM companies 
            WHERE status = 'active' 
            ORDER BY name
        ");
    }
    
    public function createInvoice() {
        $loadSheetId = $_POST['load_sheet_id'] ?? 0;
        
        if (!$loadSheetId) {
            echo json_encode(['success' => false, 'message' => 'Load sheet ID required']);
            return;
        }
        
        // Get load sheet details
        $loadSheet = $this->db->fetchOne("
            SELECT ls.*, c.name as company_name, c.payment_terms 
            FROM load_sheets ls 
            JOIN companies c ON ls.company_id = c.id 
            WHERE ls.id = ?
        ", [$loadSheetId]);
        
        if (!$loadSheet) {
            echo json_encode(['success' => false, 'message' => 'Load sheet not found']);
            return;
        }
        
        // Check if invoice already exists
        $existingInvoice = $this->db->fetchOne("
            SELECT id FROM invoices WHERE load_sheet_id = ?
        ", [$loadSheetId]);
        
        if ($existingInvoice) {
            echo json_encode(['success' => false, 'message' => 'Invoice already exists for this load sheet']);
            return;
        }
        
        // Generate invoice number
        $invoiceNumber = $this->generateInvoiceNumber();
        
        // Calculate amounts
        $subtotal = $loadSheet['final_rate'];
        $vatAmount = $subtotal * (VAT_RATE / 100);
        $totalAmount = $subtotal + $vatAmount;
        
        // Create invoice
        $invoiceData = [
            'load_sheet_id' => $loadSheetId,
            'company_id' => $loadSheet['company_id'],
            'invoice_number' => $invoiceNumber,
            'invoice_date' => date('Y-m-d'),
            'due_date' => date('Y-m-d', strtotime('+' . $loadSheet['payment_terms'] . ' days')),
            'subtotal' => $subtotal,
            'vat_rate' => VAT_RATE,
            'vat_amount' => $vatAmount,
            'total_amount' => $totalAmount,
            'payment_status' => 'pending'
        ];
        
        $invoiceId = $this->db->insert('invoices', $invoiceData);
        
        // Generate PDF
        $pdfGenerator = new PDFGenerator();
        $pdfData = $pdfGenerator->generateInvoicePDF($invoiceId, $this->db);
        
        echo json_encode([
            'success' => true,
            'invoice_id' => $invoiceId,
            'invoice_number' => $invoiceNumber,
            'total_amount' => $totalAmount,
            'pdf_url' => $pdfData['url'] ?? null
        ]);
    }
    
    public function markInvoicePaid() {
        $invoiceId = $_POST['invoice_id'] ?? 0;
        
        if (!$invoiceId) {
            echo json_encode(['success' => false, 'message' => 'Invoice ID required']);
            return;
        }
        
        $updated = $this->db->update('invoices', [
            'payment_status' => 'paid',
            'payment_date' => date('Y-m-d')
        ], 'id = ?', [$invoiceId]);
        
        if ($updated) {
            echo json_encode(['success' => true, 'message' => 'Invoice marked as paid']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update invoice']);
        }
    }
    
    public function sendInvoiceEmail() {
        $invoiceId = $_POST['invoice_id'] ?? 0;
        $emailAddress = $_POST['email_address'] ?? '';
        
        if (!$invoiceId || !$emailAddress) {
            echo json_encode(['success' => false, 'message' => 'Invoice ID and email address required']);
            return;
        }
        
        $emailSender = new EmailSender();
        $sent = $emailSender->sendInvoiceEmail($invoiceId, $emailAddress, $this->db);
        
        if ($sent) {
            echo json_encode(['success' => true, 'message' => 'Invoice sent successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to send invoice email']);
        }
    }
    
    public function getCompanyDetails() {
        $companyId = $_POST['company_id'] ?? $_GET['company_id'] ?? 0;
        
        if (!$companyId) {
            echo json_encode(['success' => false, 'message' => 'Company ID required']);
            return;
        }
        
        $company = $this->db->fetchOne("SELECT * FROM companies WHERE id = ?", [$companyId]);
        
        if ($company) {
            echo json_encode(['success' => true, 'company' => $company]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Company not found']);
        }
    }
    
    public function createSampleData() {
        // This would create additional sample data if needed
        echo json_encode(['success' => true, 'message' => 'Sample data already exists in database']);
    }
    
    private function generateInvoiceNumber() {
        $year = date('Y');
        $month = date('m');
        
        // Get the last invoice number for this month
        $lastInvoice = $this->db->fetchOne("
            SELECT invoice_number 
            FROM invoices 
            WHERE invoice_number LIKE ? 
            ORDER BY invoice_number DESC 
            LIMIT 1
        ", ["INV{$year}{$month}%"]);
        
        if ($lastInvoice) {
            $lastNumber = intval(substr($lastInvoice['invoice_number'], -3));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return 'INV' . $year . $month . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
    }
    
    public function createLoadSheet() {
        $companyId = $_POST['company_id'] ?? 0;
        $palletQuantity = $_POST['pallet_quantity'] ?? 0;
        $ratePerPallet = $_POST['rate_per_pallet'] ?? 0;
        $cargoDescription = $_POST['cargo_description'] ?? '';
        $deliveryMethod = $_POST['delivery_method'] ?? '';
        $contractorId = $_POST['contractor_id'] ?? null;
        $contractorCost = $_POST['contractor_cost'] ?? 0;
        $status = $_POST['status'] ?? 'pending';
        $date = $_POST['date'] ?? date('Y-m-d');
        
        if (!$companyId || !$palletQuantity || !$ratePerPallet || !$deliveryMethod) {
            echo json_encode(['success' => false, 'message' => 'Required fields missing']);
            return;
        }
        
        // Get contractor name if contractor is selected
        $contractorName = null;
        if ($contractorId) {
            $contractor = $this->db->fetchOne("SELECT * FROM contractors WHERE id = ?", [$contractorId]);
            if ($contractor) {
                $contractorName = $contractor['name'];
            }
        }
        
        // Convert delivery method format (own_driver -> own, contractor -> contractor)
        $deliveryMethodValue = ($deliveryMethod === 'own_driver') ? 'own' : 'contractor';
        
        // Calculate final rate
        $finalRate = $palletQuantity * $ratePerPallet;
        
        // Create load sheet data
        $loadSheetData = [
            'company_id' => $companyId,
            'pallet_quantity' => $palletQuantity,
            'cargo_description' => $cargoDescription,
            'rate_per_pallet' => $ratePerPallet,
            'final_rate' => $finalRate,
            'delivery_method' => $deliveryMethodValue,
            'contractor_name' => $contractorName,
            'contractor_cost' => $contractorCost,
            'status' => $status,
            'date' => $date,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $loadSheetId = $this->db->insert('load_sheets', $loadSheetData);
        
        if ($loadSheetId) {
            echo json_encode([
                'success' => true,
                'loadsheet_id' => $loadSheetId,
                'message' => 'Load sheet created successfully'
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to create load sheet']);
        }
    }
    
    public function getContractors() {
        $contractors = $this->db->fetchAll("SELECT * FROM contractors WHERE status = 'active' ORDER BY name");
        echo json_encode(['success' => true, 'contractors' => $contractors]);
    }
    
    public function createContractor() {
        $name = $_POST['name'] ?? '';
        $contactPerson = $_POST['contact_person'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $email = $_POST['email'] ?? '';
        
        if (!$name) {
            echo json_encode(['success' => false, 'message' => 'Contractor name is required']);
            return;
        }
        
        $contractorData = [
            'name' => $name,
            'contact_person' => $contactPerson,
            'phone' => $phone,
            'email' => $email,
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $contractorId = $this->db->insert('contractors', $contractorData);
        
        if ($contractorId) {
            $contractor = $this->db->fetchOne("SELECT * FROM contractors WHERE id = ?", [$contractorId]);
            echo json_encode([
                'success' => true,
                'contractor' => $contractor,
                'message' => 'Contractor created successfully'
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to create contractor']);
        }
    }
    
    public function saveCompany() {
        $companyId = $_POST['company_id'] ?? 0;
        $name = $_POST['name'] ?? '';
        $contactPerson = $_POST['contact_person'] ?? '';
        $email = $_POST['email'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $address = $_POST['address'] ?? '';
        $ratePerPallet = $_POST['rate_per_pallet'] ?? 0;
        $paymentTerms = $_POST['payment_terms'] ?? 30;
        $status = $_POST['status'] ?? 'active';
        
        if (!$name || !$contactPerson || !$email || !$phone || !$ratePerPallet) {
            echo json_encode(['success' => false, 'message' => 'Required fields missing']);
            return;
        }
        
        $companyData = [
            'name' => $name,
            'contact_person' => $contactPerson,
            'email' => $email,
            'phone' => $phone,
            'address' => $address,
            'rate_per_pallet' => $ratePerPallet,
            'payment_terms' => $paymentTerms,
            'status' => $status,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        if ($companyId) {
            // Update existing company
            $updated = $this->db->update('companies', $companyData, 'id = ?', [$companyId]);
            if ($updated) {
                echo json_encode(['success' => true, 'message' => 'Company updated successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to update company']);
            }
        } else {
            // Create new company
            $companyId = $this->db->insert('companies', $companyData);
            if ($companyId) {
                echo json_encode(['success' => true, 'message' => 'Company created successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to create company']);
            }
        }
    }
}
?>
