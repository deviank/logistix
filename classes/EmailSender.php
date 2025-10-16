<?php
/**
 * Email Sender Class
 */

class EmailSender {
    
    public function sendInvoiceEmail($invoiceId, $emailAddress, $db) {
        // Get invoice details
        $invoice = $db->fetchOne("
            SELECT i.*, c.name as company_name, c.contact_person, c.email as company_email
            FROM invoices i 
            JOIN companies c ON i.company_id = c.id 
            WHERE i.id = ?
        ", [$invoiceId]);
        
        if (!$invoice) {
            return false;
        }
        
        // Generate PDF
        $pdfGenerator = new PDFGenerator();
        $pdfData = $pdfGenerator->generateInvoicePDF($invoiceId, $db);
        
        if (!$pdfData) {
            return false;
        }
        
        // Email subject and content
        $subject = 'Invoice ' . $invoice['invoice_number'] . ' - ' . $invoice['company_name'];
        
        $message = $this->getInvoiceEmailTemplate($invoice);
        
        // Set headers
        $headers = [
            'From: Logistics Company <noreply@yourcompany.com>',
            'Reply-To: billing@yourcompany.com',
            'Content-Type: text/html; charset=UTF-8'
        ];
        
        // Send email (using PHP mail function - in production, use a proper email service)
        $sent = mail($emailAddress, $subject, $message, implode("\r\n", $headers));
        
        if ($sent) {
            // Update invoice to track email sent
            $db->update('invoices', [
                'email_sent_date' => date('Y-m-d H:i:s'),
                'email_sent_to' => $emailAddress
            ], 'id = ?', [$invoiceId]);
        }
        
        return $sent;
    }
    
    private function getInvoiceEmailTemplate($invoice) {
        $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #007cba; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background: #f9f9f9; }
        .invoice-details { background: white; padding: 15px; margin: 15px 0; border-left: 4px solid #007cba; }
        .footer { text-align: center; padding: 20px; font-size: 12px; color: #666; }
        .button { display: inline-block; background: #007cba; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 10px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Invoice ' . htmlspecialchars($invoice['invoice_number']) . '</h1>
        </div>
        
        <div class="content">
            <p>Dear ' . htmlspecialchars($invoice['company_name']) . ',</p>
            
            <p>Please find attached your invoice for the logistics services provided.</p>
            
            <div class="invoice-details">
                <h3>Invoice Details:</h3>
                <p><strong>Invoice Number:</strong> ' . htmlspecialchars($invoice['invoice_number']) . '</p>
                <p><strong>Invoice Date:</strong> ' . date('F j, Y', strtotime($invoice['invoice_date'])) . '</p>
                <p><strong>Due Date:</strong> ' . date('F j, Y', strtotime($invoice['due_date'])) . '</p>
                <p><strong>Total Amount:</strong> R ' . number_format($invoice['total_amount'], 2) . '</p>
                <p><strong>Payment Status:</strong> ' . ucfirst($invoice['payment_status']) . '</p>
            </div>
            
            <p>Please process payment within the specified terms. If you have any questions about this invoice, please don\'t hesitate to contact us.</p>
            
            <p>Thank you for your business!</p>
            
            <p>Best regards,<br>
            Logistics Team</p>
        </div>
        
        <div class="footer">
            <p>This is an automated email. Please do not reply to this message.</p>
            <p>For support, contact us at support@yourcompany.com</p>
        </div>
    </div>
</body>
</html>';
        
        return $html;
    }
}
?>
