<?php
/**
 * Email Sender Class using PHPMailer
 */

// Check if PHPMailer is installed via Composer
if (file_exists(ROOT_PATH . 'vendor/autoload.php')) {
    require_once ROOT_PATH . 'vendor/autoload.php';
} else {
    // Fallback: try to load PHPMailer manually if not using Composer
    // You can download PHPMailer and place it in a 'phpmailer' folder
    $phpmailerPath = ROOT_PATH . 'phpmailer/src/Exception.php';
    if (file_exists($phpmailerPath)) {
        require_once ROOT_PATH . 'phpmailer/src/Exception.php';
        require_once ROOT_PATH . 'phpmailer/src/PHPMailer.php';
        require_once ROOT_PATH . 'phpmailer/src/SMTP.php';
    }
}

// Use PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailSender {
    
    /**
     * Get PHPMailer instance with SMTP configuration
     */
    private function getMailer() {
        $mail = new PHPMailer(true);
        
        try {
            // Check if SMTP is enabled
            if (!defined('SMTP_ENABLED') || !SMTP_ENABLED) {
                throw new Exception('SMTP is not enabled. Please configure email settings in config/config.php');
            }
            
            // Server settings
            $mail->isSMTP();
            $mail->Host = defined('SMTP_HOST') ? SMTP_HOST : 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = defined('SMTP_USERNAME') ? SMTP_USERNAME : '';
            $mail->Password = defined('SMTP_PASSWORD') ? SMTP_PASSWORD : '';
            $mail->SMTPSecure = defined('SMTP_SECURE') ? SMTP_SECURE : 'tls';
            $mail->Port = defined('SMTP_PORT') ? SMTP_PORT : 587;
            
            // Enable verbose debug output (for testing - remove in production)
            // $mail->SMTPDebug = 2;
            
            // Character encoding
            $mail->CharSet = 'UTF-8';
            
            // From address
            $fromEmail = defined('SMTP_FROM_EMAIL') ? SMTP_FROM_EMAIL : (defined('COMPANY_EMAIL') ? COMPANY_EMAIL : 'noreply@logisticscompany.co.za');
            $fromName = defined('SMTP_FROM_NAME') ? SMTP_FROM_NAME : (defined('COMPANY_NAME') ? COMPANY_NAME : 'Logistics Management System');
            $mail->setFrom($fromEmail, $fromName);
            
            return $mail;
        } catch (Exception $e) {
            throw new Exception('Failed to initialize email: ' . $e->getMessage());
        }
    }
    
    public function sendInvoiceEmail($invoiceId, $emailAddress, $db) {
        try {
            // Get invoice details
            $invoice = $db->fetchOne("
                SELECT i.*, c.name as company_name, c.contact_person, c.email as company_email
                FROM invoices i 
                JOIN companies c ON i.company_id = c.id 
                WHERE i.id = ?
            ", [$invoiceId]);
            
            if (!$invoice) {
                throw new Exception('Invoice not found');
            }
            
            // Generate PDF
            $pdfGenerator = new PDFGenerator();
            $pdfData = $pdfGenerator->generateInvoicePDF($invoiceId, $db);
            
            if (!$pdfData) {
                throw new Exception('Failed to generate invoice PDF');
            }
            
            // Get PHPMailer instance
            $mail = $this->getMailer();
            
            // Recipients
            $mail->addAddress($emailAddress, $invoice['company_name']);
            
            // Add reply-to
            if (!empty($invoice['company_email'])) {
                $mail->addReplyTo($invoice['company_email'], $invoice['company_name']);
            }
            
            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Invoice ' . $invoice['invoice_number'] . ' - ' . $invoice['company_name'];
            $mail->Body = $this->getInvoiceEmailTemplate($invoice);
            $mail->AltBody = strip_tags($this->getInvoiceEmailTemplate($invoice));
            
            // Attach PDF if file exists
            if (isset($pdfData['filepath']) && file_exists($pdfData['filepath'])) {
                $mail->addAttachment($pdfData['filepath'], 'Invoice-' . $invoice['invoice_number'] . '.html');
            }
            
            // Send email
            $mail->send();
            
            // Update invoice to track email sent
            $db->update('invoices', [
                'email_sent_date' => date('Y-m-d H:i:s'),
                'email_sent_to' => $emailAddress
            ], 'id = ?', [$invoiceId]);
            
            return true;
        } catch (Exception $e) {
            error_log('Invoice email error: ' . $e->getMessage());
            throw $e;
        }
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
    
    public function sendStatementEmail($statementId, $emailAddress, $db) {
        try {
            // Get statement details
            $statement = $db->fetchOne("
                SELECT s.*, c.name as company_name, c.contact_person, c.email as company_email
                FROM statements s 
                JOIN companies c ON s.company_id = c.id 
                WHERE s.id = ?
            ", [$statementId]);
            
            if (!$statement) {
                throw new Exception('Statement not found');
            }
            
            // Generate PDF
            $pdfGenerator = new PDFGenerator();
            $pdfData = $pdfGenerator->generateStatementPDF($statementId, $db);
            
            if (!$pdfData) {
                throw new Exception('Failed to generate statement PDF');
            }
            
            // Get PHPMailer instance
            $mail = $this->getMailer();
            
            // Recipients
            $mail->addAddress($emailAddress, $statement['company_name']);
            
            // Add reply-to
            if (!empty($statement['company_email'])) {
                $mail->addReplyTo($statement['company_email'], $statement['company_name']);
            }
            
            // Email subject and content
            $statementNumber = 'STMT' . date('Ym', strtotime($statement['statement_date'])) . str_pad($statementId, 3, '0', STR_PAD_LEFT);
            
            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Monthly Statement ' . $statementNumber . ' - ' . $statement['company_name'];
            $mail->Body = $this->getStatementEmailTemplate($statement, $statementNumber);
            $mail->AltBody = strip_tags($this->getStatementEmailTemplate($statement, $statementNumber));
            
            // Attach PDF if file exists
            if (isset($pdfData['filepath']) && file_exists($pdfData['filepath'])) {
                $mail->addAttachment($pdfData['filepath'], 'Statement-' . $statementNumber . '.html');
            }
            
            // Send email
            $mail->send();
            
            // Update statement to track email sent
            $db->update('statements', [
                'email_sent_date' => date('Y-m-d H:i:s'),
                'email_sent' => 1
            ], 'id = ?', [$statementId]);
            
            return true;
        } catch (Exception $e) {
            error_log('Statement email error: ' . $e->getMessage());
            throw $e;
        }
    }
    
    private function getStatementEmailTemplate($statement, $statementNumber) {
        $period = date('F Y', strtotime($statement['statement_period'] . '-01'));
        
        $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #007cba; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background: #f9f9f9; }
        .statement-details { background: white; padding: 15px; margin: 15px 0; border-left: 4px solid #007cba; }
        .footer { text-align: center; padding: 20px; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Monthly Statement ' . htmlspecialchars($statementNumber) . '</h1>
        </div>
        
        <div class="content">
            <p>Dear ' . htmlspecialchars($statement['company_name']) . ',</p>
            
            <p>Please find attached your monthly statement for ' . htmlspecialchars($period) . '.</p>
            
            <div class="statement-details">
                <h3>Statement Summary:</h3>
                <p><strong>Statement Number:</strong> ' . htmlspecialchars($statementNumber) . '</p>
                <p><strong>Period:</strong> ' . htmlspecialchars($period) . '</p>
                <p><strong>Opening Balance:</strong> R ' . number_format($statement['opening_balance'], 2) . '</p>
                <p><strong>Total Charges:</strong> R ' . number_format($statement['total_charges'], 2) . '</p>
                <p><strong>Total Payments:</strong> R ' . number_format($statement['total_payments'], 2) . '</p>
                <p><strong>Closing Balance:</strong> R ' . number_format($statement['closing_balance'], 2) . '</p>
            </div>
            
            <p>Please review the attached statement. If you have any questions, please don\'t hesitate to contact us.</p>
            
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
