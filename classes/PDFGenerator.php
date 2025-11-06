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
        $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Invoice ' . htmlspecialchars($invoice['invoice_number']) . '</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            margin: 0; 
            padding: 20px; 
            background: #f5f5f5;
        }
        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header { 
            text-align: center; 
            margin-bottom: 30px; 
            border-bottom: 2px solid #007cba;
            padding-bottom: 20px;
        }
        .header h1 {
            color: #007cba;
            margin: 0;
            font-size: 28px;
        }
        .header h2 {
            color: #333;
            margin: 5px 0 0 0;
            font-size: 18px;
        }
        .company-info { 
            float: right; 
            text-align: right; 
            margin-bottom: 20px;
        }
        .company-info h3 {
            color: #007cba;
            margin: 0 0 10px 0;
        }
        .invoice-details { 
            margin: 20px 0; 
            clear: both;
        }
        .invoice-details h3 {
            color: #333;
            margin-bottom: 10px;
        }
        .invoice-table { 
            width: 100%; 
            border-collapse: collapse; 
            margin: 20px 0; 
        }
        .invoice-table th, .invoice-table td { 
            border: 1px solid #ddd; 
            padding: 12px; 
            text-align: left; 
        }
        .invoice-table th { 
            background-color: #007cba; 
            color: white;
            font-weight: bold;
        }
        .invoice-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .total-section { 
            text-align: right; 
            margin-top: 20px; 
            background: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
        }
        .total-section p {
            margin: 5px 0;
            font-size: 16px;
        }
        .total-amount {
            font-size: 20px;
            font-weight: bold;
            color: #007cba;
            border-top: 2px solid #007cba;
            padding-top: 10px;
            margin-top: 10px;
        }
        .footer { 
            margin-top: 40px; 
            text-align: center; 
            font-size: 12px; 
            color: #666; 
            border-top: 1px solid #ddd;
            padding-top: 20px;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        .status-paid {
            background: #d4edda;
            color: #155724;
        }
        @media print { 
            body { margin: 0; background: white; }
            .invoice-container { box-shadow: none; }
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <div class="header">
            <h1>INVOICE</h1>
            <h2>' . htmlspecialchars($invoice['invoice_number']) . '</h2>
        </div>
        
        <div class="company-info">
            <h3>Logistics Company</h3>
            <p>Your Company Name<br>
            Your Address<br>
            Your City, Postal Code<br>
            Phone: Your Phone<br>
            Email: Your Email</p>
        </div>
        
        <div class="invoice-details">
            <h3>Bill To:</h3>
            <p><strong>' . htmlspecialchars($invoice['company_name']) . '</strong><br>';
        
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
            $html .= 'VAT Number: ' . htmlspecialchars($invoice['vat_number']) . '<br>';
        }
        
        $statusClass = $invoice['payment_status'] === 'paid' ? 'status-paid' : 'status-pending';
        
        $html .= '</p>
        </div>
        
        <div class="invoice-details">
            <p><strong>Invoice Date:</strong> ' . date('F j, Y', strtotime($invoice['invoice_date'])) . '</p>
            <p><strong>Due Date:</strong> ' . date('F j, Y', strtotime($invoice['due_date'])) . '</p>
            <p><strong>Payment Status:</strong> <span class="status-badge ' . $statusClass . '">' . ucfirst($invoice['payment_status']) . '</span></p>
        </div>
        
        <table class="invoice-table">
            <thead>
                <tr>
                    <th>Description</th>
                    <th>Pickup Location</th>
                    <th>Delivery Location</th>
                    <th>Pallets</th>
                    <th>Weight (kg)</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>' . htmlspecialchars($invoice['cargo_description']) . '</td>
                    <td>' . htmlspecialchars($invoice['pickup_location']) . '</td>
                    <td>' . htmlspecialchars($invoice['delivery_location']) . '</td>
                    <td>' . htmlspecialchars($invoice['pallet_quantity']) . '</td>
                    <td>' . htmlspecialchars($invoice['cargo_weight']) . '</td>
                    <td>R ' . number_format($invoice['subtotal'], 2) . '</td>
                </tr>
            </tbody>
        </table>
        
        <div class="total-section">
            <p><strong>Subtotal: R ' . number_format($invoice['subtotal'], 2) . '</strong></p>
            <p>VAT (' . $invoice['vat_rate'] . '%): R ' . number_format($invoice['vat_amount'], 2) . '</p>
            <p class="total-amount">Total Amount: R ' . number_format($invoice['total_amount'], 2) . '</p>
        </div>
        
        <div class="footer">
            <p><strong>Thank you for your business!</strong></p>
            <p>Payment terms: ' . $invoice['payment_terms'] . ' days from invoice date</p>
            <p>For any queries, please contact us at your-email@company.com</p>
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
        
        $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Statement ' . htmlspecialchars($statementNumber) . '</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            margin: 0; 
            padding: 20px; 
            background: #f5f5f5;
        }
        .statement-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header { 
            text-align: center; 
            margin-bottom: 30px; 
            border-bottom: 2px solid #007cba;
            padding-bottom: 20px;
        }
        .header h1 {
            color: #007cba;
            margin: 0;
            font-size: 28px;
        }
        .header h2 {
            color: #333;
            margin: 5px 0 0 0;
            font-size: 18px;
        }
        .company-info { 
            float: right; 
            text-align: right; 
            margin-bottom: 20px;
        }
        .statement-details { 
            margin: 20px 0; 
            clear: both;
        }
        .statement-table { 
            width: 100%; 
            border-collapse: collapse; 
            margin: 20px 0; 
        }
        .statement-table th, .statement-table td { 
            border: 1px solid #ddd; 
            padding: 12px; 
            text-align: left; 
        }
        .statement-table th { 
            background-color: #007cba; 
            color: white;
            font-weight: bold;
        }
        .summary-section { 
            text-align: right; 
            margin-top: 20px; 
            background: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
        }
        .summary-section p {
            margin: 5px 0;
            font-size: 16px;
        }
        .closing-balance {
            font-size: 20px;
            font-weight: bold;
            color: #007cba;
            border-top: 2px solid #007cba;
            padding-top: 10px;
            margin-top: 10px;
        }
        .footer { 
            margin-top: 40px; 
            text-align: center; 
            font-size: 12px; 
            color: #666; 
            border-top: 1px solid #ddd;
            padding-top: 20px;
        }
    </style>
</head>
<body>
    <div class="statement-container">
        <div class="header">
            <h1>MONTHLY STATEMENT</h1>
            <h2>' . htmlspecialchars($statementNumber) . '</h2>
            <p>Period: ' . htmlspecialchars($period) . '</p>
        </div>
        
        <div class="company-info">
            <h3>Logistics Company</h3>
            <p>Your Company Name<br>
            Your Address<br>
            Your City, Postal Code</p>
        </div>
        
        <div class="statement-details">
            <h3>Bill To:</h3>
            <p><strong>' . htmlspecialchars($statement['company_name']) . '</strong><br>';
        
        if (!empty($statement['billing_address'])) {
            $html .= nl2br(htmlspecialchars($statement['billing_address'])) . '<br>';
        }
        
        $html .= '</p>
        </div>
        
        <table class="statement-table">
            <thead>
                <tr>
                    <th>Invoice #</th>
                    <th>Date</th>
                    <th>Due Date</th>
                    <th>Amount</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>';
        
        foreach ($items as $item) {
            $statusClass = $item['payment_status'] === 'paid' ? 'status-paid' : 'status-pending';
            $html .= '<tr>
                <td>' . htmlspecialchars($item['invoice_number']) . '</td>
                <td>' . date('M j, Y', strtotime($item['invoice_date'])) . '</td>
                <td>' . date('M j, Y', strtotime($item['due_date'])) . '</td>
                <td>R ' . number_format($item['amount'], 2) . '</td>
                <td><span class="status-badge ' . $statusClass . '">' . ucfirst($item['payment_status']) . '</span></td>
            </tr>';
        }
        
        $html .= '</tbody>
        </table>
        
        <div class="summary-section">
            <p><strong>Opening Balance:</strong> R ' . number_format($statement['opening_balance'], 2) . '</p>
            <p><strong>Total Charges:</strong> R ' . number_format($statement['total_charges'], 2) . '</p>
            <p><strong>Total Payments:</strong> R ' . number_format($statement['total_payments'], 2) . '</p>
            <p class="closing-balance">Closing Balance: R ' . number_format($statement['closing_balance'], 2) . '</p>
        </div>
        
        <div class="footer">
            <p><strong>Thank you for your business!</strong></p>
            <p>For any queries, please contact us at your-email@company.com</p>
        </div>
    </div>
</body>
</html>';
        
        return $html;
    }
}
?>
