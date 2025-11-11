<?php
/**
 * PDF Generator Class
 */

class PDFGenerator {
    
    public function generateInvoicePDF($invoiceId, $db) {
        // Get invoice details
        $invoice = $db->fetchOne("
            SELECT i.*, c.name as company_name, c.contact_person, c.email, c.phone, 
                   c.billing_address, c.vat_number,
                   ls.pickup_location, ls.delivery_location, ls.cargo_description, 
                   ls.pallet_quantity, ls.cargo_weight
            FROM invoices i 
            JOIN companies c ON i.company_id = c.id 
            JOIN load_sheets ls ON i.load_sheet_id = ls.id
            WHERE i.id = ?
        ", [$invoiceId]);
        
        if (!$invoice) {
            return false;
        }
        
        // Generate HTML content
        $html = $this->getInvoiceHTML($invoice);
        
        // Save HTML file
        $filename = 'invoice-' . $invoice['invoice_number'] . '.html';
        $filepath = UPLOADS_PATH . $filename;
        
        file_put_contents($filepath, $html);
        
        return [
            'filepath' => $filepath,
            'url' => APP_URL . '/uploads/' . $filename,
            'filename' => $filename
        ];
    }
    
    private function getInvoiceHTML($invoice) {
        $invoiceDate = date('F j, Y', strtotime($invoice['invoice_date']));
        $dueDate = date('F j, Y', strtotime($invoice['due_date']));
        $statusClass = $invoice['payment_status'] === 'paid' ? 'status-paid' : 'status-pending';
        $statusText = ucfirst($invoice['payment_status']);
        
        $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice ' . htmlspecialchars($invoice['invoice_number']) . '</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body { 
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            font-size: 14px;
            line-height: 1.6;
            color: #333;
            background: #f5f5f5;
            padding: 20px;
        }
        .invoice-container {
            max-width: 900px;
            margin: 0 auto;
            background: #ffffff;
            padding: 50px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .header-section {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 40px;
            padding-bottom: 30px;
            border-bottom: 3px solid #2563eb;
        }
        .logo-section {
            flex: 1;
        }
        .logo-section h1 {
            font-size: 32px;
            font-weight: 700;
            color: #2563eb;
            margin-bottom: 5px;
            letter-spacing: -0.5px;
        }
        .logo-section .company-name {
            font-size: 18px;
            color: #1e40af;
            font-weight: 600;
            margin-bottom: 15px;
        }
        .logo-section .company-details {
            font-size: 13px;
            color: #666;
            line-height: 1.8;
        }
        .invoice-info {
            text-align: right;
            flex: 0 0 250px;
        }
        .invoice-info .invoice-label {
            font-size: 36px;
            font-weight: 700;
            color: #2563eb;
            margin-bottom: 10px;
            letter-spacing: -1px;
        }
        .invoice-info .invoice-number {
            font-size: 18px;
            color: #666;
            margin-bottom: 20px;
            font-weight: 600;
        }
        .invoice-info .info-row {
            font-size: 13px;
            margin-bottom: 8px;
            color: #555;
        }
        .invoice-info .info-label {
            font-weight: 600;
            color: #333;
            display: inline-block;
            min-width: 90px;
        }
        .billing-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-bottom: 40px;
            padding: 25px;
            background: #f8fafc;
            border-radius: 8px;
        }
        .bill-to, .company-info {
            padding: 0;
        }
        .bill-to h3, .company-info h3 {
            font-size: 14px;
            font-weight: 700;
            color: #1e40af;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 2px solid #2563eb;
            display: inline-block;
        }
        .bill-to p, .company-info p {
            font-size: 13px;
            line-height: 1.8;
            color: #333;
        }
        .bill-to strong {
            font-size: 16px;
            color: #1e293b;
            display: block;
            margin-bottom: 8px;
        }
        .invoice-table { 
            width: 100%; 
            border-collapse: collapse; 
            margin: 30px 0;
            background: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        .invoice-table thead {
            background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
        }
        .invoice-table th { 
            padding: 16px 12px;
            text-align: left;
            font-weight: 600;
            font-size: 13px;
            color: #ffffff;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .invoice-table td { 
            padding: 16px 12px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 13px;
            color: #333;
        }
        .invoice-table tbody tr:hover {
            background-color: #f8fafc;
        }
        .invoice-table tbody tr:last-child td {
            border-bottom: none;
        }
        .invoice-table .text-right {
            text-align: right;
        }
        .invoice-table .text-center {
            text-align: center;
        }
        .total-section { 
            margin-top: 30px;
            margin-left: auto;
            width: 350px;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            font-size: 14px;
            color: #555;
        }
        .total-row.total-label {
            font-weight: 600;
            color: #333;
        }
        .total-row.grand-total {
            margin-top: 15px;
            padding-top: 20px;
            border-top: 3px solid #2563eb;
            font-size: 20px;
            font-weight: 700;
            color: #2563eb;
        }
        .status-badge {
            display: inline-block;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }
        .status-paid {
            background: #d1fae5;
            color: #065f46;
        }
        .footer { 
            margin-top: 50px; 
            padding-top: 30px;
            border-top: 2px solid #e5e7eb;
            text-align: center;
        }
        .footer p {
            font-size: 12px;
            color: #666;
            margin-bottom: 8px;
            line-height: 1.6;
        }
        .footer .thank-you {
            font-size: 16px;
            font-weight: 600;
            color: #2563eb;
            margin-bottom: 15px;
        }
        .footer .contact-info {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #e5e7eb;
            color: #888;
        }
        @media print { 
            body { 
                margin: 0; 
                padding: 0;
                background: white;
            }
            .invoice-container { 
                box-shadow: none;
                padding: 30px;
            }
            .header-section {
                page-break-inside: avoid;
            }
            .invoice-table {
                page-break-inside: avoid;
            }
            .total-section {
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <div class="header-section">
            <div class="logo-section">
                <h1>INVOICE</h1>
                <div class="company-name">' . htmlspecialchars(COMPANY_NAME) . '</div>
                <div class="company-details">
                    ' . htmlspecialchars(COMPANY_ADDRESS) . '<br>
                    ' . htmlspecialchars(COMPANY_CITY) . '<br>
                    Phone: ' . htmlspecialchars(COMPANY_PHONE) . '<br>
                    Email: ' . htmlspecialchars(COMPANY_EMAIL) . '<br>';
        
        if (defined('COMPANY_VAT_NUMBER') && COMPANY_VAT_NUMBER) {
            $html .= 'VAT: ' . htmlspecialchars(COMPANY_VAT_NUMBER) . '<br>';
        }
        if (defined('COMPANY_REGISTRATION') && COMPANY_REGISTRATION) {
            $html .= htmlspecialchars(COMPANY_REGISTRATION);
        }
        
        $html .= '</div>
            </div>
            <div class="invoice-info">
                <div class="invoice-label">INVOICE</div>
                <div class="invoice-number">' . htmlspecialchars($invoice['invoice_number']) . '</div>
                <div class="info-row">
                    <span class="info-label">Date:</span>
                    <span>' . $invoiceDate . '</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Due Date:</span>
                    <span>' . $dueDate . '</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Status:</span>
                    <span class="status-badge ' . $statusClass . '">' . $statusText . '</span>
                </div>
            </div>
        </div>
        
        <div class="billing-section">
            <div class="bill-to">
                <h3>Bill To</h3>
                <p>
                    <strong>' . htmlspecialchars($invoice['company_name']) . '</strong><br>';
        
        if (!empty($invoice['billing_address'])) {
            $html .= nl2br(htmlspecialchars($invoice['billing_address'])) . '<br>';
        }
        
        if (!empty($invoice['contact_person'])) {
            $html .= 'Contact: ' . htmlspecialchars($invoice['contact_person']) . '<br>';
        }
        
        if (!empty($invoice['email'])) {
            $html .= 'Email: ' . htmlspecialchars($invoice['email']) . '<br>';
        }
        
        if (!empty($invoice['phone'])) {
            $html .= 'Phone: ' . htmlspecialchars($invoice['phone']) . '<br>';
        }
        
        if (!empty($invoice['vat_number'])) {
            $html .= 'VAT: ' . htmlspecialchars($invoice['vat_number']) . '<br>';
        }
        
        $html .= '</p>
            </div>
            <div class="company-info">
                <h3>Payment Information</h3>
                <p>
                    Payment Terms: ' . ($invoice['payment_terms'] ?? 30) . ' days<br>
                    Payment Method: Bank Transfer<br>
                    Please quote invoice number when making payment
                </p>
            </div>
        </div>
        
        <table class="invoice-table">
            <thead>
                <tr>
                    <th style="width: 25%;">Description</th>
                    <th style="width: 20%;">Pickup Location</th>
                    <th style="width: 20%;">Delivery Location</th>
                    <th style="width: 10%;" class="text-center">Pallets</th>
                    <th style="width: 10%;" class="text-center">Weight (kg)</th>
                    <th style="width: 15%;" class="text-right">Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>' . htmlspecialchars($invoice['cargo_description'] ?: 'Logistics Services') . '</td>
                    <td>' . htmlspecialchars($invoice['pickup_location'] ?: '-') . '</td>
                    <td>' . htmlspecialchars($invoice['delivery_location'] ?: '-') . '</td>
                    <td class="text-center">' . htmlspecialchars($invoice['pallet_quantity']) . '</td>
                    <td class="text-center">' . htmlspecialchars($invoice['cargo_weight'] ?: '-') . '</td>
                    <td class="text-right"><strong>R ' . number_format($invoice['subtotal'], 2) . '</strong></td>
                </tr>
            </tbody>
        </table>
        
        <div class="total-section">
            <div class="total-row">
                <span>Subtotal:</span>
                <span><strong>R ' . number_format($invoice['subtotal'], 2) . '</strong></span>
            </div>
            <div class="total-row">
                <span>VAT (' . $invoice['vat_rate'] . '%):</span>
                <span><strong>R ' . number_format($invoice['vat_amount'], 2) . '</strong></span>
            </div>
            <div class="total-row grand-total">
                <span>Total Amount:</span>
                <span>R ' . number_format($invoice['total_amount'], 2) . '</span>
            </div>
        </div>
        
        <div class="footer">
            <p class="thank-you">Thank you for your business!</p>
            <p>Payment is due within ' . ($invoice['payment_terms'] ?? 30) . ' days from the invoice date.</p>
            <p>Please ensure payment is made by the due date to avoid any delays.</p>
            <div class="contact-info">
                <p>For any queries regarding this invoice, please contact us:</p>
                <p><strong>' . htmlspecialchars(COMPANY_EMAIL) . '</strong> | <strong>' . htmlspecialchars(COMPANY_PHONE) . '</strong></p>
            </div>
        </div>
    </div>
</body>
</html>';
        
        return $html;
    }
    
    public function generateStatementPDF($statementId, $db) {
        // Get statement details
        $statement = $db->fetchOne("
            SELECT s.*, c.name as company_name, c.contact_person, c.email, c.phone, c.billing_address
            FROM statements s 
            JOIN companies c ON s.company_id = c.id 
            WHERE s.id = ?
        ", [$statementId]);
        
        if (!$statement) {
            return false;
        }
        
        // Get statement items
        $items = $db->fetchAll("
            SELECT si.*, i.invoice_date, i.due_date
            FROM statement_items si
            JOIN invoices i ON si.invoice_id = i.id
            WHERE si.statement_id = ?
            ORDER BY si.invoice_date ASC
        ", [$statementId]);
        
        // Generate HTML content
        $html = $this->getStatementHTML($statement, $items);
        
        // Save HTML file
        $statementNumber = 'STMT' . date('Ym', strtotime($statement['statement_date'])) . str_pad($statementId, 3, '0', STR_PAD_LEFT);
        $filename = 'statement-' . $statementNumber . '.html';
        $filepath = UPLOADS_PATH . $filename;
        
        file_put_contents($filepath, $html);
        
        return [
            'filepath' => $filepath,
            'url' => APP_URL . '/uploads/' . $filename,
            'filename' => $filename
        ];
    }
    
    private function getStatementHTML($statement, $items) {
        $statementNumber = 'STMT' . date('Ym', strtotime($statement['statement_date'])) . str_pad($statement['id'], 3, '0', STR_PAD_LEFT);
        $period = date('F Y', strtotime($statement['statement_period'] . '-01'));
        $statementDate = date('F j, Y', strtotime($statement['statement_date']));
        
        $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statement ' . htmlspecialchars($statementNumber) . '</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body { 
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            font-size: 14px;
            line-height: 1.6;
            color: #333;
            background: #f5f5f5;
            padding: 20px;
        }
        .statement-container {
            max-width: 900px;
            margin: 0 auto;
            background: #ffffff;
            padding: 50px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .header-section {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 40px;
            padding-bottom: 30px;
            border-bottom: 3px solid #2563eb;
        }
        .logo-section {
            flex: 1;
        }
        .logo-section h1 {
            font-size: 32px;
            font-weight: 700;
            color: #2563eb;
            margin-bottom: 5px;
            letter-spacing: -0.5px;
        }
        .logo-section .company-name {
            font-size: 18px;
            color: #1e40af;
            font-weight: 600;
            margin-bottom: 15px;
        }
        .logo-section .company-details {
            font-size: 13px;
            color: #666;
            line-height: 1.8;
        }
        .statement-info {
            text-align: right;
            flex: 0 0 280px;
        }
        .statement-info .statement-label {
            font-size: 36px;
            font-weight: 700;
            color: #2563eb;
            margin-bottom: 10px;
            letter-spacing: -1px;
        }
        .statement-info .statement-number {
            font-size: 18px;
            color: #666;
            margin-bottom: 15px;
            font-weight: 600;
        }
        .statement-info .period {
            font-size: 16px;
            color: #1e40af;
            font-weight: 600;
            margin-bottom: 20px;
            padding: 8px 15px;
            background: #eff6ff;
            border-radius: 6px;
            display: inline-block;
        }
        .statement-info .info-row {
            font-size: 13px;
            margin-bottom: 8px;
            color: #555;
        }
        .statement-info .info-label {
            font-weight: 600;
            color: #333;
            display: inline-block;
            min-width: 100px;
        }
        .billing-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-bottom: 40px;
            padding: 25px;
            background: #f8fafc;
            border-radius: 8px;
        }
        .bill-to, .company-info {
            padding: 0;
        }
        .bill-to h3, .company-info h3 {
            font-size: 14px;
            font-weight: 700;
            color: #1e40af;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 2px solid #2563eb;
            display: inline-block;
        }
        .bill-to p, .company-info p {
            font-size: 13px;
            line-height: 1.8;
            color: #333;
        }
        .bill-to strong {
            font-size: 16px;
            color: #1e293b;
            display: block;
            margin-bottom: 8px;
        }
        .statement-table { 
            width: 100%; 
            border-collapse: collapse; 
            margin: 30px 0;
            background: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        .statement-table thead {
            background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
        }
        .statement-table th { 
            padding: 16px 12px;
            text-align: left;
            font-weight: 600;
            font-size: 13px;
            color: #ffffff;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .statement-table td { 
            padding: 16px 12px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 13px;
            color: #333;
        }
        .statement-table tbody tr:hover {
            background-color: #f8fafc;
        }
        .statement-table tbody tr:last-child td {
            border-bottom: none;
        }
        .statement-table .text-right {
            text-align: right;
        }
        .statement-table .text-center {
            text-align: center;
        }
        .summary-section { 
            margin-top: 30px;
            margin-left: auto;
            width: 400px;
            padding: 25px;
            background: #f8fafc;
            border-radius: 8px;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            font-size: 14px;
            color: #555;
            border-bottom: 1px solid #e5e7eb;
        }
        .summary-row:last-child {
            border-bottom: none;
        }
        .summary-row strong {
            font-weight: 600;
            color: #333;
        }
        .summary-row.closing-balance {
            margin-top: 15px;
            padding-top: 20px;
            border-top: 3px solid #2563eb;
            font-size: 20px;
            font-weight: 700;
            color: #2563eb;
        }
        .status-badge {
            display: inline-block;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }
        .status-paid {
            background: #d1fae5;
            color: #065f46;
        }
        .footer { 
            margin-top: 50px; 
            padding-top: 30px;
            border-top: 2px solid #e5e7eb;
            text-align: center;
        }
        .footer p {
            font-size: 12px;
            color: #666;
            margin-bottom: 8px;
            line-height: 1.6;
        }
        .footer .thank-you {
            font-size: 16px;
            font-weight: 600;
            color: #2563eb;
            margin-bottom: 15px;
        }
        .footer .contact-info {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #e5e7eb;
            color: #888;
        }
        @media print { 
            body { 
                margin: 0; 
                padding: 0;
                background: white;
            }
            .statement-container { 
                box-shadow: none;
                padding: 30px;
            }
            .header-section {
                page-break-inside: avoid;
            }
            .statement-table {
                page-break-inside: avoid;
            }
            .summary-section {
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>
    <div class="statement-container">
        <div class="header-section">
            <div class="logo-section">
                <h1>MONTHLY STATEMENT</h1>
                <div class="company-name">' . htmlspecialchars(COMPANY_NAME) . '</div>
                <div class="company-details">
                    ' . htmlspecialchars(COMPANY_ADDRESS) . '<br>
                    ' . htmlspecialchars(COMPANY_CITY) . '<br>
                    Phone: ' . htmlspecialchars(COMPANY_PHONE) . '<br>
                    Email: ' . htmlspecialchars(COMPANY_EMAIL) . '<br>';
        
        if (defined('COMPANY_VAT_NUMBER') && COMPANY_VAT_NUMBER) {
            $html .= 'VAT: ' . htmlspecialchars(COMPANY_VAT_NUMBER) . '<br>';
        }
        if (defined('COMPANY_REGISTRATION') && COMPANY_REGISTRATION) {
            $html .= htmlspecialchars(COMPANY_REGISTRATION);
        }
        
        $html .= '</div>
            </div>
            <div class="statement-info">
                <div class="statement-label">STATEMENT</div>
                <div class="statement-number">' . htmlspecialchars($statementNumber) . '</div>
                <div class="period">' . htmlspecialchars($period) . '</div>
                <div class="info-row">
                    <span class="info-label">Statement Date:</span>
                    <span>' . $statementDate . '</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Invoices:</span>
                    <span>' . count($items) . '</span>
                </div>
            </div>
        </div>
        
        <div class="billing-section">
            <div class="bill-to">
                <h3>Bill To</h3>
                <p>
                    <strong>' . htmlspecialchars($statement['company_name']) . '</strong><br>';
        
        if (!empty($statement['billing_address'])) {
            $html .= nl2br(htmlspecialchars($statement['billing_address'])) . '<br>';
        }
        
        if (!empty($statement['contact_person'])) {
            $html .= 'Contact: ' . htmlspecialchars($statement['contact_person']) . '<br>';
        }
        
        if (!empty($statement['email'])) {
            $html .= 'Email: ' . htmlspecialchars($statement['email']) . '<br>';
        }
        
        if (!empty($statement['phone'])) {
            $html .= 'Phone: ' . htmlspecialchars($statement['phone']) . '<br>';
        }
        
        $html .= '</p>
            </div>
            <div class="company-info">
                <h3>Account Summary</h3>
                <p>
                    This statement reflects all transactions<br>
                    for the period ' . htmlspecialchars($period) . '.<br><br>
                    Please review all invoices listed below<br>
                    and ensure payment is made promptly.
                </p>
            </div>
        </div>
        
        <table class="statement-table">
            <thead>
                <tr>
                    <th style="width: 20%;">Invoice #</th>
                    <th style="width: 18%;">Invoice Date</th>
                    <th style="width: 18%;">Due Date</th>
                    <th style="width: 24%;" class="text-right">Amount</th>
                    <th style="width: 20%;" class="text-center">Status</th>
                </tr>
            </thead>
            <tbody>';
        
        foreach ($items as $item) {
            $statusClass = $item['payment_status'] === 'paid' ? 'status-paid' : 'status-pending';
            $invoiceDate = date('M j, Y', strtotime($item['invoice_date']));
            $dueDate = date('M j, Y', strtotime($item['due_date']));
            
            $html .= '<tr>
                <td><strong>' . htmlspecialchars($item['invoice_number']) . '</strong></td>
                <td>' . $invoiceDate . '</td>
                <td>' . $dueDate . '</td>
                <td class="text-right"><strong>R ' . number_format($item['amount'], 2) . '</strong></td>
                <td class="text-center"><span class="status-badge ' . $statusClass . '">' . ucfirst($item['payment_status']) . '</span></td>
            </tr>';
        }
        
        $html .= '</tbody>
        </table>
        
        <div class="summary-section">
            <div class="summary-row">
                <span>Opening Balance:</span>
                <span><strong>R ' . number_format($statement['opening_balance'], 2) . '</strong></span>
            </div>
            <div class="summary-row">
                <span>Total Charges:</span>
                <span><strong>R ' . number_format($statement['total_charges'], 2) . '</strong></span>
            </div>
            <div class="summary-row">
                <span>Total Payments:</span>
                <span><strong>R ' . number_format($statement['total_payments'], 2) . '</strong></span>
            </div>
            <div class="summary-row closing-balance">
                <span>Closing Balance:</span>
                <span>R ' . number_format($statement['closing_balance'], 2) . '</span>
            </div>
        </div>
        
        <div class="footer">
            <p class="thank-you">Thank you for your business!</p>
            <p>Please ensure all outstanding amounts are settled promptly.</p>
            <p>If you have any questions about this statement, please contact us immediately.</p>
            <div class="contact-info">
                <p>For any queries regarding this statement, please contact us:</p>
                <p><strong>' . htmlspecialchars(COMPANY_EMAIL) . '</strong> | <strong>' . htmlspecialchars(COMPANY_PHONE) . '</strong></p>
            </div>
        </div>
    </div>
</body>
</html>';
        
        return $html;
    }
}
?>
