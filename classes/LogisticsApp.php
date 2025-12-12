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
        $showInactive = isset($_GET['show_inactive']) && $_GET['show_inactive'] == '1';
        if ($showInactive) {
            $companies = $this->db->fetchAll("
                SELECT c.*, 
                       COUNT(i.id) as invoice_count
                FROM companies c
                LEFT JOIN invoices i ON i.company_id = c.id
                GROUP BY c.id
                ORDER BY c.status DESC, c.name
            ");
        } else {
            $companies = $this->db->fetchAll("
                SELECT c.*, 
                       COUNT(i.id) as invoice_count
                FROM companies c
                LEFT JOIN invoices i ON i.company_id = c.id
                WHERE c.status = 'active'
                GROUP BY c.id
                ORDER BY c.name
            ");
        }
        include TEMPLATES_PATH . 'companies.php';
    }
    
    public function showLoadSheets() {
        $loadSheets = $this->db->fetchAll("
            SELECT ls.*, c.name as company_name,
                   i.id as invoice_id,
                   i.invoice_number
            FROM load_sheets ls 
            JOIN companies c ON ls.company_id = c.id 
            LEFT JOIN invoices i ON i.load_sheet_id = ls.id
            ORDER BY ls.created_at DESC
        ");
        
        // Map status values for display
        $statusMap = [
            'draft' => 'pending',
            'confirmed' => 'in_progress',
            'completed' => 'completed'
        ];
        foreach ($loadSheets as &$ls) {
            $ls['status'] = $statusMap[$ls['status']] ?? $ls['status'];
        }
        
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
            SELECT s.*, c.name as company_name,
                   CONCAT('STMT', YEAR(s.statement_date), LPAD(MONTH(s.statement_date), 2, '0'), LPAD(s.id, 3, '0')) as statement_number,
                   s.statement_period as statement_month,
                   s.total_charges as total_amount,
                   CASE WHEN s.closing_balance > 0 THEN 'pending' ELSE 'paid' END as status
            FROM statements s 
            JOIN companies c ON s.company_id = c.id 
            ORDER BY s.created_at DESC
        ");
        include TEMPLATES_PATH . 'statements.php';
    }
    
    public function handleAjax() {
        // Set JSON header
        header('Content-Type: application/json');
        
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
            case 'generate_dummy_invoices':
                $this->generateDummyInvoiceData();
                break;
            case 'generate_dummy_companies':
                $this->generateDummyCompanies();
                break;
            case 'remove_duplicate_companies':
                $this->removeDuplicateCompanies();
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
            case 'toggle_company_status':
                $this->toggleCompanyStatus();
                break;
            case 'delete_company':
                $this->deleteCompany();
                break;
            case 'get_invoice_details':
                $this->getInvoiceDetails();
                break;
            case 'get_loadsheet_details':
                $this->getLoadSheetDetails();
                break;
            case 'get_statement_details':
                $this->getStatementDetails();
                break;
            case 'generate_statement':
                $this->generateStatement();
                break;
            case 'download_invoice':
                $this->downloadInvoice();
                break;
            case 'download_statement':
                $this->downloadStatement();
                break;
            case 'send_statement_email':
                $this->sendStatementEmail();
                break;
            default:
                http_response_code(404);
                echo json_encode(['error' => 'Action not found']);
                break;
        }
    }
    
    private function getDashboardStats() {
        $stats = [];
        
        // Invoices this month - count invoices created this month (using created_at)
        $result = $this->db->fetchOne("
            SELECT COUNT(*) as count 
            FROM invoices 
            WHERE DATE_FORMAT(created_at, '%Y-%m') = DATE_FORMAT(CURRENT_DATE(), '%Y-%m')
        ");
        $stats['invoices_this_month'] = $result ? (int)$result['count'] : 0;
        
        // Outstanding balance - include both 'pending' and 'overdue' statuses, 
        // or invoices that are past due date (even if still marked as pending)
        $result = $this->db->fetchOne("
            SELECT COALESCE(SUM(total_amount), 0) as balance 
            FROM invoices 
            WHERE payment_status IN ('pending', 'overdue')
               OR (payment_status = 'pending' AND due_date < CURDATE())
        ");
        $stats['outstanding_balance'] = $result ? (float)$result['balance'] : 0.00;
        
        // Active companies
        $result = $this->db->fetchOne("
            SELECT COUNT(*) as count 
            FROM companies 
            WHERE status = 'active'
        ");
        $stats['active_companies'] = $result ? (int)$result['count'] : 0;
        
        return $stats;
    }
    
    private function getRecentLoadSheets() {
        return $this->db->fetchAll("
            SELECT ls.*, c.name as company_name,
                   i.id as invoice_id,
                   i.invoice_number,
                   i.payment_status as invoice_payment_status
            FROM load_sheets ls 
            JOIN companies c ON ls.company_id = c.id 
            LEFT JOIN invoices i ON i.load_sheet_id = ls.id
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
        
        try {
            $emailSender = new EmailSender();
            $sent = $emailSender->sendInvoiceEmail($invoiceId, $emailAddress, $this->db);
            
            if ($sent) {
                echo json_encode(['success' => true, 'message' => 'Invoice sent successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to send invoice email']);
            }
        } catch (Exception $e) {
            // Email might have been sent but error occurred (e.g., during DB update)
            // Check if email was actually sent by checking error message
            $errorMsg = $e->getMessage();
            if (strpos($errorMsg, 'SMTP') === false && strpos($errorMsg, 'authenticate') === false) {
                // Likely a non-critical error (e.g., DB update failed but email sent)
                echo json_encode(['success' => true, 'message' => 'Invoice sent successfully (note: ' . $errorMsg . ')']);
            } else {
                // Critical SMTP error
                echo json_encode(['success' => false, 'message' => 'Error: ' . $errorMsg]);
            }
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
            // Get invoice count
            $invoiceCount = $this->db->fetchOne("
                SELECT COUNT(*) as count 
                FROM invoices 
                WHERE company_id = ?
            ", [$companyId]);
            $company['invoice_count'] = $invoiceCount['count'] ?? 0;
            
            // Get recent invoices (last 10)
            $invoices = $this->db->fetchAll("
                SELECT invoice_number, invoice_date, total_amount, payment_status
                FROM invoices 
                WHERE company_id = ?
                ORDER BY invoice_date DESC
                LIMIT 10
            ", [$companyId]);
            $company['recent_invoices'] = $invoices;
            
            echo json_encode(['success' => true, 'company' => $company]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Company not found']);
        }
    }
    
    public function createSampleData() {
        // This would create additional sample data if needed
        echo json_encode(['success' => true, 'message' => 'Sample data already exists in database']);
    }
    
    public function generateDummyInvoiceData() {
        try {
            // Get all active companies
            $companies = $this->db->fetchAll("SELECT * FROM companies WHERE status = 'active'");
            
            if (empty($companies)) {
                echo json_encode(['success' => false, 'message' => 'No active companies found']);
                return;
            }
            
            $totalLoadSheets = 0;
            $totalInvoices = 0;
            $startDate = date('Y-m-d', strtotime('-2 years'));
            $endDate = date('Y-m-d');
            
            // Sample locations for variety
            $pickupLocations = [
                'Johannesburg Warehouse', 'Cape Town Distribution Center', 'Durban Port',
                'Pretoria Storage Facility', 'Port Elizabeth Depot', 'Bloemfontein Hub',
                'East London Terminal', 'Nelspruit Warehouse', 'Polokwane Distribution'
            ];
            
            $deliveryLocations = [
                'Spar Store, Sandton', 'Pick n Pay, Cape Town', 'Woolworths, Durban',
                'Checkers, Pretoria', 'Shoprite, Port Elizabeth', 'Makro, Bloemfontein',
                'Game, East London', 'Builders Warehouse, Nelspruit', 'Clicks, Polokwane'
            ];
            
            $cargoDescriptions = [
                'Grocery items and household goods', 'Fresh produce and dairy products',
                'Beverages and snacks', 'Electronics and appliances', 'Furniture and home goods',
                'Clothing and textiles', 'Building materials', 'Automotive parts',
                'Pharmaceutical products', 'Office supplies'
            ];
            
            foreach ($companies as $company) {
                // Random number of invoices per company (8-40 over 2 years)
                $numInvoices = rand(8, 40);
                
                // Generate invoices spread over 2 years
                for ($i = 0; $i < $numInvoices; $i++) {
                    // Random date within the past 2 years (more recent dates slightly more likely)
                    $daysAgo = rand(0, 730); // 0 to 730 days ago
                    $invoiceDate = date('Y-m-d', strtotime("-{$daysAgo} days"));
                    
                    // Random pallet quantity (1-15 pallets)
                    $palletQuantity = rand(1, 15);
                    
                    // Use company's rate or add some variation (±20%)
                    $rateVariation = 1 + (rand(-20, 20) / 100);
                    $ratePerPallet = $company['rate_per_pallet'] * $rateVariation;
                    $finalRate = $palletQuantity * $ratePerPallet;
                    
                    // Random delivery method (70% own, 30% contractor)
                    $useContractor = rand(1, 100) <= 30;
                    $deliveryMethod = $useContractor ? 'contractor' : 'own';
                    $contractorCost = $useContractor ? $finalRate * (rand(50, 80) / 100) : 0;
                    
                    // Random status (more completed than draft)
                    $statusRand = rand(1, 100);
                    if ($statusRand <= 70) {
                        $status = 'completed';
                    } elseif ($statusRand <= 85) {
                        $status = 'confirmed';
                    } else {
                        $status = 'draft';
                    }
                    
                    // Create load sheet
                    $loadSheetData = [
                        'company_id' => $company['id'],
                        'pickup_location' => $pickupLocations[array_rand($pickupLocations)],
                        'delivery_location' => $deliveryLocations[array_rand($deliveryLocations)],
                        'cargo_description' => $cargoDescriptions[array_rand($cargoDescriptions)],
                        'special_instructions' => rand(1, 3) === 1 ? 'Handle with care - fragile items' : null,
                        'pallet_quantity' => $palletQuantity,
                        'cargo_weight' => round($palletQuantity * rand(200, 500), 2),
                        'delivery_method' => $deliveryMethod,
                        'contractor_name' => $useContractor ? 'Contractor ' . rand(1, 5) : null,
                        'contractor_cost' => $contractorCost,
                        'rate_per_pallet' => $ratePerPallet,
                        'final_rate' => $finalRate,
                        'requested_date' => $invoiceDate,
                        'status' => $status,
                        'created_at' => $invoiceDate . ' ' . date('H:i:s', rand(8 * 3600, 17 * 3600))
                    ];
                    
                    $loadSheetId = $this->db->insert('load_sheets', $loadSheetData);
                    $totalLoadSheets++;
                    
                    // Only create invoice if load sheet is completed or confirmed
                    if ($status !== 'draft') {
                        // Generate invoice number for the specific date
                        $invoiceNumber = $this->generateInvoiceNumberForDate($invoiceDate);
                        
                        // Calculate amounts
                        $subtotal = $finalRate;
                        $vatAmount = round($subtotal * (VAT_RATE / 100), 2);
                        $totalAmount = $subtotal + $vatAmount;
                        
                        // Random payment status (60% paid, 30% pending, 10% overdue)
                        $paymentRand = rand(1, 100);
                        if ($paymentRand <= 60) {
                            $paymentStatus = 'paid';
                            // Payment date is between invoice date and now (or due date)
                            $dueDate = date('Y-m-d', strtotime($invoiceDate . ' +' . $company['payment_terms'] . ' days'));
                            $maxPaymentDate = min($dueDate, $endDate);
                            $daysAfterInvoice = rand(0, min(60, (strtotime($maxPaymentDate) - strtotime($invoiceDate)) / 86400));
                            $paymentDate = date('Y-m-d', strtotime($invoiceDate . ' +' . $daysAfterInvoice . ' days'));
                        } elseif ($paymentRand <= 90) {
                            $paymentStatus = 'pending';
                            $paymentDate = null;
                        } else {
                            $paymentStatus = 'overdue';
                            $paymentDate = null;
                        }
                        
                        $dueDate = date('Y-m-d', strtotime($invoiceDate . ' +' . $company['payment_terms'] . ' days'));
                        
                        // Create invoice
                        $invoiceData = [
                            'load_sheet_id' => $loadSheetId,
                            'company_id' => $company['id'],
                            'invoice_number' => $invoiceNumber,
                            'invoice_date' => $invoiceDate,
                            'due_date' => $dueDate,
                            'subtotal' => $subtotal,
                            'vat_rate' => VAT_RATE,
                            'vat_amount' => $vatAmount,
                            'total_amount' => $totalAmount,
                            'payment_status' => $paymentStatus,
                            'payment_date' => $paymentDate,
                            'created_at' => $invoiceDate . ' ' . date('H:i:s', rand(8 * 3600, 17 * 3600))
                        ];
                        
                        $this->db->insert('invoices', $invoiceData);
                        $totalInvoices++;
                    }
                }
            }
            
            echo json_encode([
                'success' => true, 
                'message' => "Generated {$totalLoadSheets} load sheets and {$totalInvoices} invoices for " . count($companies) . " companies",
                'load_sheets' => $totalLoadSheets,
                'invoices' => $totalInvoices,
                'companies' => count($companies)
            ]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error generating dummy data: ' . $e->getMessage()]);
        }
    }
    
    private function generateInvoiceNumberForDate($date) {
        $year = date('Y', strtotime($date));
        $month = date('m', strtotime($date));
        
        // Get all invoice numbers for this specific month/year
        $existingInvoices = $this->db->fetchAll("
            SELECT invoice_number 
            FROM invoices 
            WHERE invoice_number LIKE ? 
            ORDER BY invoice_number DESC
        ", ["INV{$year}{$month}%"]);
        
        // Find the highest number used
        $maxNumber = 0;
        foreach ($existingInvoices as $inv) {
            $number = intval(substr($inv['invoice_number'], -3));
            if ($number > $maxNumber) {
                $maxNumber = $number;
            }
        }
        
        // Use next sequential number
        $newNumber = $maxNumber + 1;
        
        // If no existing invoices, start from a random number (1-50) to make it look realistic
        if ($maxNumber === 0) {
            $newNumber = rand(1, 50);
        }
        
        $invoiceNumber = 'INV' . $year . $month . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
        
        // Double-check uniqueness (in case of race condition)
        $check = $this->db->fetchOne("SELECT id FROM invoices WHERE invoice_number = ?", [$invoiceNumber]);
        if ($check) {
            // If exists, increment and try again
            $newNumber++;
            $invoiceNumber = 'INV' . $year . $month . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
        }
        
        return $invoiceNumber;
    }
    
    public function generateDummyCompanies() {
        try {
            // South African company names
            $companyNames = [
                'Cape Town Logistics', 'Johannesburg Freight Solutions', 'Durban Transport Co',
                'Pretoria Distribution', 'Port Elizabeth Shipping', 'Bloemfontein Logistics',
                'East London Transport', 'Nelspruit Freight Services', 'Polokwane Distribution',
                'Kimberley Logistics', 'Rustenburg Transport', 'Witbank Freight Solutions',
                'Pietermaritzburg Logistics', 'George Transport Co', 'Potchefstroom Distribution',
                'Welkom Freight Services', 'Klerksdorp Logistics', 'Upington Transport',
                'Middelburg Freight', 'Vereeniging Logistics', 'Sasolburg Transport',
                'Springs Distribution', 'Benoni Freight Solutions', 'Boksburg Logistics',
                'Germiston Transport', 'Kempton Park Freight', 'Alberton Distribution',
                'Randburg Logistics', 'Sandton Transport Co', 'Roodepoort Freight'
            ];
            
            // South African first names
            $firstNames = [
                'Thabo', 'Sipho', 'Lungile', 'Nomsa', 'Bongani', 'Zanele', 'Mandla', 'Thandi',
                'Sibusiso', 'Ntombi', 'Mpho', 'Lindiwe', 'Kagiso', 'Nolwazi', 'Tshepo', 'Zinhle',
                'John', 'Sarah', 'Michael', 'Jennifer', 'David', 'Lisa', 'James', 'Michelle',
                'Robert', 'Amanda', 'William', 'Nicole', 'Richard', 'Jessica', 'Daniel', 'Ashley'
            ];
            
            // South African last names
            $lastNames = [
                'Mthembu', 'Ndlovu', 'Khumalo', 'Dlamini', 'Mkhize', 'Nkosi', 'Zulu', 'Molefe',
                'Smith', 'Johnson', 'Williams', 'Brown', 'Jones', 'Garcia', 'Miller', 'Davis',
                'Rodriguez', 'Martinez', 'Hernandez', 'Lopez', 'Wilson', 'Anderson', 'Thomas', 'Taylor'
            ];
            
            // South African cities and addresses
            $cities = [
                'Johannesburg', 'Cape Town', 'Durban', 'Pretoria', 'Port Elizabeth', 'Bloemfontein',
                'East London', 'Nelspruit', 'Polokwane', 'Kimberley', 'Rustenburg', 'Witbank',
                'Pietermaritzburg', 'George', 'Potchefstroom', 'Welkom', 'Klerksdorp', 'Upington'
            ];
            
            $streets = [
                'Main Road', 'Church Street', 'High Street', 'Market Street', 'Long Street',
                'Victoria Street', 'King Street', 'Queen Street', 'Oxford Road', 'Rivonia Road',
                'Jan Smuts Avenue', 'William Nicol Drive', 'Louis Botha Avenue', 'Jan Hofmeyr Road'
            ];
            
            // Company types/suffixes
            $companyTypes = ['Pty Ltd', 'Ltd', 'CC', 'Inc', 'Logistics', 'Transport', 'Freight', 'Distribution'];
            
            $numCompanies = rand(10, 25); // Generate 10-25 companies
            $created = 0;
            $skipped = 0;
            
            for ($i = 0; $i < $numCompanies; $i++) {
                // Random company name
                $baseName = $companyNames[array_rand($companyNames)];
                $companyType = $companyTypes[array_rand($companyTypes)];
                $companyName = $baseName . ' ' . $companyType;
                
                // Check if company already exists
                $existing = $this->db->fetchOne("SELECT id FROM companies WHERE name = ?", [$companyName]);
                if ($existing) {
                    $skipped++;
                    continue;
                }
                
                // Random contact person
                $firstName = $firstNames[array_rand($firstNames)];
                $lastName = $lastNames[array_rand($lastNames)];
                $contactPerson = $firstName . ' ' . $lastName;
                
                // Generate email (South African format)
                $emailDomains = ['co.za', 'com', 'net', 'org.za'];
                $emailDomain = $emailDomains[array_rand($emailDomains)];
                $emailPrefix = strtolower(str_replace(' ', '', $firstName . '.' . $lastName));
                $email = $emailPrefix . '@' . (rand(1, 100) > 50 ? 'example.' : '') . $emailDomain;
                
                // South African phone number format
                $areaCodes = ['011', '021', '031', '012', '041', '051', '043', '013', '015', '053', '014', '016', '033', '044', '018', '057', '018', '054'];
                $areaCode = $areaCodes[array_rand($areaCodes)];
                $phoneNumber = rand(100, 999) . ' ' . rand(1000, 9999);
                $phone = $areaCode . '-' . $phoneNumber;
                
                // Generate address
                $streetNumber = rand(1, 999);
                $street = $streets[array_rand($streets)];
                $city = $cities[array_rand($cities)];
                $postalCode = rand(1000, 9999);
                $billingAddress = $streetNumber . ' ' . $street . ', ' . $city . ', ' . $postalCode;
                
                // Generate VAT number (South African format: 10 digits starting with 4)
                $vatNumber = '4' . str_pad(rand(0, 999999999), 9, '0', STR_PAD_LEFT);
                
                // Payment terms (mostly 30 days, some 15, 45, 60)
                $paymentTermsOptions = [15, 30, 30, 30, 30, 45, 60]; // Weighted towards 30
                $paymentTerms = $paymentTermsOptions[array_rand($paymentTermsOptions)];
                
                // Rate per pallet (R 500 - R 5000)
                $ratePerPallet = round(rand(50000, 500000) / 100, 2);
                
                // Status (mostly active, some inactive)
                $status = rand(1, 100) <= 85 ? 'active' : 'inactive';
                
                // Random creation date within the past year
                $daysAgo = rand(0, 365);
                $createdAt = date('Y-m-d H:i:s', strtotime("-{$daysAgo} days"));
                
                $companyData = [
                    'name' => $companyName,
                    'contact_person' => $contactPerson,
                    'email' => $email,
                    'phone' => $phone,
                    'billing_address' => $billingAddress,
                    'vat_number' => $vatNumber,
                    'payment_terms' => $paymentTerms,
                    'rate_per_pallet' => $ratePerPallet,
                    'status' => $status,
                    'created_at' => $createdAt
                ];
                
                $companyId = $this->db->insert('companies', $companyData);
                if ($companyId) {
                    $created++;
                }
            }
            
            echo json_encode([
                'success' => true,
                'message' => "Generated {$created} new companies" . ($skipped > 0 ? " ({$skipped} skipped - already exist)" : ''),
                'created' => $created,
                'skipped' => $skipped
            ]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error generating companies: ' . $e->getMessage()]);
        }
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
        $loadSheetId = $_POST['loadsheet_id'] ?? 0;
        
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
        
        // Map status values (template uses pending/in_progress/completed, DB uses draft/confirmed/completed)
        $statusMap = [
            'pending' => 'draft',
            'in_progress' => 'confirmed',
            'completed' => 'completed'
        ];
        $statusValue = $statusMap[$status] ?? 'draft';
        
        // Calculate final rate
        $finalRate = $palletQuantity * $ratePerPallet;
        
        // Create or update load sheet data
        $loadSheetData = [
            'company_id' => $companyId,
            'pallet_quantity' => $palletQuantity,
            'cargo_description' => $cargoDescription,
            'rate_per_pallet' => $ratePerPallet,
            'final_rate' => $finalRate,
            'delivery_method' => $deliveryMethodValue,
            'contractor_name' => $contractorName,
            'contractor_cost' => $contractorCost,
            'status' => $statusValue,
            'requested_date' => $date
        ];
        
        if ($loadSheetId) {
            // Update existing load sheet
            $updated = $this->db->update('load_sheets', $loadSheetData, 'id = ?', [$loadSheetId]);
            if ($updated) {
                echo json_encode([
                    'success' => true,
                    'loadsheet_id' => $loadSheetId,
                    'message' => 'Load sheet updated successfully'
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to update load sheet']);
            }
            return;
        }
        
        // Create new load sheet (created_at is auto-set by MySQL)
        try {
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
        } catch (Exception $e) {
            error_log('Error creating load sheet: ' . $e->getMessage());
            echo json_encode([
                'success' => false, 
                'message' => 'Database error: ' . $e->getMessage()
            ]);
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
            // DB column is billing_address; form field comes as 'address'
            'billing_address' => $address,
            'rate_per_pallet' => $ratePerPallet,
            'payment_terms' => $paymentTerms,
            'status' => $status
        ];
        
        if ($companyId) {
            // Update existing company (don't update created_at)
            $updated = $this->db->update('companies', $companyData, 'id = ?', [$companyId]);
            if ($updated) {
                echo json_encode(['success' => true, 'message' => 'Company updated successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to update company']);
            }
        } else {
            // Create new company
            $companyData['created_at'] = date('Y-m-d H:i:s');
            $companyId = $this->db->insert('companies', $companyData);
            if ($companyId) {
                echo json_encode(['success' => true, 'message' => 'Company created successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to create company']);
            }
        }
    }
    
    public function toggleCompanyStatus() {
        $companyId = $_POST['company_id'] ?? 0;
        
        if (!$companyId) {
            echo json_encode(['success' => false, 'message' => 'Company ID required']);
            return;
        }
        
        // Get current status
        $company = $this->db->fetchOne("SELECT status FROM companies WHERE id = ?", [$companyId]);
        if (!$company) {
            echo json_encode(['success' => false, 'message' => 'Company not found']);
            return;
        }
        
        $newStatus = ($company['status'] === 'active') ? 'inactive' : 'active';
        
        $updated = $this->db->update('companies', ['status' => $newStatus], 'id = ?', [$companyId]);
        
        if ($updated) {
            echo json_encode([
                'success' => true, 
                'message' => 'Company ' . $newStatus . 'd successfully',
                'new_status' => $newStatus
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update company status']);
        }
    }
    
    public function deleteCompany() {
        $companyId = $_POST['company_id'] ?? 0;
        
        if (!$companyId) {
            echo json_encode(['success' => false, 'message' => 'Company ID required']);
            return;
        }
        
        // Get company status
        $company = $this->db->fetchOne("SELECT status FROM companies WHERE id = ?", [$companyId]);
        if (!$company) {
            echo json_encode(['success' => false, 'message' => 'Company not found']);
            return;
        }
        
        // Allow deletion of inactive companies
        if ($company['status'] === 'inactive') {
            $deleted = $this->db->delete('companies', 'id = ?', [$companyId]);
            
            if ($deleted) {
                echo json_encode(['success' => true, 'message' => 'Company deleted successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to delete company']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Only inactive companies can be deleted']);
        }
    }
    
    public function removeDuplicateCompanies() {
        try {
            // Get all companies with their data counts
            $allCompanies = $this->db->fetchAll("
                SELECT c.*,
                       (SELECT COUNT(*) FROM invoices WHERE company_id = c.id) as invoice_count,
                       (SELECT COUNT(*) FROM load_sheets WHERE company_id = c.id) as loadsheet_count,
                       (SELECT COUNT(*) FROM statements WHERE company_id = c.id) as statement_count
                FROM companies c
                ORDER BY c.name
            ");
            
            // Group companies by base name (without common suffixes)
            $groups = [];
            foreach ($allCompanies as $company) {
                $name = strtolower(trim($company['name']));
                // Remove common suffixes to find base name
                $baseName = preg_replace('/\s+(pty\s+ltd|ltd|cc|inc|logistics|transport|freight|distribution|solutions|co)$/i', '', $name);
                $baseName = trim($baseName);
                
                if (!isset($groups[$baseName])) {
                    $groups[$baseName] = [];
                }
                $groups[$baseName][] = $company;
            }
            
            $deleted = 0;
            $kept = 0;
            $groupsProcessed = 0;
            
            foreach ($groups as $baseName => $companies) {
                if (count($companies) <= 1) {
                    continue; // Skip if only one company in group
                }
                
                $groupsProcessed++;
                
                // Sort by: most data first, then by creation date (oldest first)
                usort($companies, function($a, $b) {
                    $aScore = ($a['invoice_count'] * 3) + ($a['loadsheet_count'] * 2) + ($a['statement_count'] * 2);
                    $bScore = ($b['invoice_count'] * 3) + ($b['loadsheet_count'] * 2) + ($b['statement_count'] * 2);
                    
                    if ($aScore !== $bScore) {
                        return $bScore - $aScore; // Higher score first
                    }
                    
                    // If same score, keep the oldest one
                    return strtotime($a['created_at']) - strtotime($b['created_at']);
                });
                
                // Keep the first one (best company), delete the rest
                $keepId = $companies[0]['id'];
                $kept++;
                
                for ($i = 1; $i < count($companies); $i++) {
                    $deleteId = $companies[$i]['id'];
                    // Set status to inactive first (safer approach)
                    $this->db->update('companies', ['status' => 'inactive'], 'id = ?', [$deleteId]);
                    // Then delete (CASCADE will handle related records)
                    $this->db->delete('companies', 'id = ?', [$deleteId]);
                    $deleted++;
                }
            }
            
            if ($deleted === 0) {
                echo json_encode(['success' => true, 'message' => 'No duplicate companies found', 'deleted' => 0]);
                return;
            }
            
            echo json_encode([
                'success' => true,
                'message' => "Removed {$deleted} duplicate companies from {$groupsProcessed} groups. Kept {$kept} companies.",
                'deleted' => $deleted,
                'kept' => $kept,
                'groups' => $groupsProcessed
            ]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error removing duplicates: ' . $e->getMessage()]);
        }
    }
    
    public function getInvoiceDetails() {
        $invoiceId = $_POST['invoice_id'] ?? $_GET['invoice_id'] ?? 0;
        
        if (!$invoiceId) {
            echo json_encode(['success' => false, 'message' => 'Invoice ID required']);
            return;
        }
        
        $invoice = $this->db->fetchOne("
            SELECT i.*, c.name as company_name, c.contact_person, c.email, c.phone, 
                   c.billing_address, c.vat_number,
                   ls.pallet_quantity, ls.cargo_description, ls.delivery_method,
                   ls.contractor_name, ls.contractor_cost
            FROM invoices i 
            JOIN companies c ON i.company_id = c.id 
            JOIN load_sheets ls ON i.load_sheet_id = ls.id
            WHERE i.id = ?
        ", [$invoiceId]);
        
        if ($invoice) {
            echo json_encode(['success' => true, 'invoice' => $invoice]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invoice not found']);
        }
    }
    
    public function getLoadSheetDetails() {
        $loadSheetId = $_POST['loadsheet_id'] ?? $_GET['loadsheet_id'] ?? 0;
        
        if (!$loadSheetId) {
            echo json_encode(['success' => false, 'message' => 'Load sheet ID required']);
            return;
        }
        
        $loadSheet = $this->db->fetchOne("
            SELECT ls.*, c.name as company_name, c.rate_per_pallet as company_rate_per_pallet,
                   c.payment_terms, i.id as invoice_id, i.invoice_number
            FROM load_sheets ls 
            JOIN companies c ON ls.company_id = c.id 
            LEFT JOIN invoices i ON i.load_sheet_id = ls.id
            WHERE ls.id = ?
        ", [$loadSheetId]);
        
        if ($loadSheet) {
            // Map status back to template values
            $statusMap = [
                'draft' => 'pending',
                'confirmed' => 'in_progress',
                'completed' => 'completed'
            ];
            $loadSheet['status'] = $statusMap[$loadSheet['status']] ?? $loadSheet['status'];
            
            // Map delivery method back
            $loadSheet['delivery_method'] = $loadSheet['delivery_method'] === 'own' ? 'own_driver' : 'contractor';
            
            echo json_encode(['success' => true, 'loadsheet' => $loadSheet]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Load sheet not found']);
        }
    }
    
    public function getStatementDetails() {
        $statementId = $_POST['statement_id'] ?? $_GET['statement_id'] ?? 0;
        
        if (!$statementId) {
            echo json_encode(['success' => false, 'message' => 'Statement ID required']);
            return;
        }
        
        $statement = $this->db->fetchOne("
            SELECT s.*, c.name as company_name, c.contact_person, c.email, c.phone, c.billing_address
            FROM statements s 
            JOIN companies c ON s.company_id = c.id 
            WHERE s.id = ?
        ", [$statementId]);
        
        if ($statement) {
            // Get statement items (invoices)
            $items = $this->db->fetchAll("
                SELECT si.*, i.invoice_date, i.due_date
                FROM statement_items si
                JOIN invoices i ON si.invoice_id = i.id
                WHERE si.statement_id = ?
                ORDER BY si.invoice_date ASC
            ", [$statementId]);
            
            $statement['items'] = $items;
            echo json_encode(['success' => true, 'statement' => $statement]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Statement not found']);
        }
    }
    
    public function generateStatement() {
        $companyId = $_POST['company_id'] ?? 0;
        $month = $_POST['month'] ?? '';
        $notes = $_POST['notes'] ?? '';
        
        if (!$companyId || !$month) {
            echo json_encode(['success' => false, 'message' => 'Company ID and month required']);
            return;
        }
        
        // Parse month (format: YYYY-MM)
        $year = substr($month, 0, 4);
        $monthNum = substr($month, 5, 2);
        
        // Get all invoices for this company in this month
        $invoices = $this->db->fetchAll("
            SELECT * FROM invoices 
            WHERE company_id = ? 
            AND YEAR(invoice_date) = ? 
            AND MONTH(invoice_date) = ?
            ORDER BY invoice_date ASC
        ", [$companyId, $year, $monthNum]);
        
        if (empty($invoices)) {
            echo json_encode(['success' => false, 'message' => 'No invoices found for this period']);
            return;
        }
        
        // Calculate totals
        $totalCharges = 0;
        $totalPayments = 0;
        foreach ($invoices as $invoice) {
            $totalCharges += $invoice['total_amount'];
            if ($invoice['payment_status'] === 'paid') {
                $totalPayments += $invoice['total_amount'];
            }
        }
        
        // Get opening balance (previous month's closing balance)
        $prevMonth = date('Y-m', strtotime($month . '-01 -1 month'));
        $prevStatement = $this->db->fetchOne("
            SELECT closing_balance FROM statements 
            WHERE company_id = ? AND statement_period = ?
            ORDER BY created_at DESC LIMIT 1
        ", [$companyId, $prevMonth]);
        
        $openingBalance = $prevStatement ? $prevStatement['closing_balance'] : 0;
        $closingBalance = $openingBalance + $totalCharges - $totalPayments;
        
        // Create statement (statement_number will be computed in queries)
        $statementData = [
            'company_id' => $companyId,
            'statement_period' => $month,
            'statement_date' => date('Y-m-d'),
            'opening_balance' => $openingBalance,
            'total_charges' => $totalCharges,
            'total_payments' => $totalPayments,
            'closing_balance' => $closingBalance,
            'invoice_count' => count($invoices)
        ];
        
        $statementId = $this->db->insert('statements', $statementData);
        
        // Generate statement number after insert
        $statementNumber = 'STMT' . $year . $monthNum . str_pad($statementId, 3, '0', STR_PAD_LEFT);
        
        if ($statementId) {
            // Create statement items
            foreach ($invoices as $invoice) {
                $this->db->insert('statement_items', [
                    'statement_id' => $statementId,
                    'invoice_id' => $invoice['id'],
                    'invoice_date' => $invoice['invoice_date'],
                    'invoice_number' => $invoice['invoice_number'],
                    'amount' => $invoice['total_amount'],
                    'payment_status' => $invoice['payment_status'],
                    'payment_date' => $invoice['payment_date']
                ]);
            }
            
            // Generate PDF
            $pdfGenerator = new PDFGenerator();
            $pdfData = $pdfGenerator->generateStatementPDF($statementId, $this->db);
            
            echo json_encode([
                'success' => true,
                'statement_id' => $statementId,
                'statement_number' => $statementNumber,
                'pdf_url' => $pdfData['url'] ?? null,
                'message' => 'Statement generated successfully'
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to create statement']);
        }
    }
    
    public function downloadInvoice() {
        $invoiceId = $_GET['invoice_id'] ?? 0;
        
        if (!$invoiceId) {
            http_response_code(400);
            echo json_encode(['error' => 'Invoice ID required']);
            return;
        }
        
        $pdfGenerator = new PDFGenerator();
        $pdfData = $pdfGenerator->generateInvoicePDF($invoiceId, $this->db);
        
        if ($pdfData && isset($pdfData['url'])) {
            header('Location: ' . $pdfData['url']);
            exit;
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Invoice PDF not found']);
        }
    }
    
    public function downloadStatement() {
        $statementId = $_GET['statement_id'] ?? 0;
        
        if (!$statementId) {
            http_response_code(400);
            echo json_encode(['error' => 'Statement ID required']);
            return;
        }
        
        $pdfGenerator = new PDFGenerator();
        $pdfData = $pdfGenerator->generateStatementPDF($statementId, $this->db);
        
        if ($pdfData && isset($pdfData['url'])) {
            header('Location: ' . $pdfData['url']);
            exit;
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Statement PDF not found']);
        }
    }
    
    public function sendStatementEmail() {
        $statementId = $_POST['statement_id'] ?? 0;
        $emailAddress = $_POST['email_address'] ?? '';
        
        if (!$statementId || !$emailAddress) {
            echo json_encode(['success' => false, 'message' => 'Statement ID and email address required']);
            return;
        }
        
        // Validate email address
        if (!filter_var($emailAddress, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'Invalid email address']);
            return;
        }
        
        try {
            $emailSender = new EmailSender();
            $sent = $emailSender->sendStatementEmail($statementId, $emailAddress, $this->db);
            
            if ($sent) {
                echo json_encode(['success' => true, 'message' => 'Statement sent successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to send statement email. Please check your email configuration.']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }
}
?>
