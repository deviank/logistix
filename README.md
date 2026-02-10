# Logistics Management System

A lightweight, custom PHP application for managing logistics operations with a streamlined workflow: **Load Sheet → Invoice → Monthly Statement**.

## Features

- **Unified Dashboard** - Single entry point for all operations
- **Company Management** - Customer database with billing details
- **Load Sheet Creation** - Streamlined data entry with auto-population
- **Automatic Invoice Generation** - PDF invoices with email delivery
- **Contractor Cost Tracking** - Internal expense tracking for profit calculation
- **Monthly Statements** - Automated statement generation
- **Real-time Profit Calculation** - Visual profit/loss indicators

## Quick Setup

### 1. Database Setup

1. Create a MySQL database named `logistics_db`
2. Import the database schema:
   ```bash
   mysql -u root -p logistics_db < setup.sql
   ```

### 2. Configuration

1. Update database credentials in `config/database.php`:
   ```php
   private $host = 'localhost';
   private $db_name = 'logistics_db';
   private $username = 'your_username';
   private $password = 'your_password';
   ```

2. Update application URL in `config/config.php`:
   ```php
   define('APP_URL', 'http://your-domain.com/logistics-app');
   ```

### 3. File Permissions

Ensure the uploads directory is writable:
```bash
chmod 755 uploads/
```

### 4. Access the Application

Navigate to: `http://your-domain.com/logistics-app`

## Sample Data

The application comes with sample data including:
- 3 Company accounts (Spar, Pick n Pay, Woolworths)
- Sample load sheets with different delivery methods
- Sample invoices with mixed payment status

## Workflow

### 1. Create Load Sheet
- Select company from dropdown
- System auto-fills company details
- Enter pallet quantity and date
- Choose delivery method (own driver or contractor)
- System calculates profit/loss

### 2. Generate Invoice
- Click "Create Invoice" on completed load sheets
- System generates PDF invoice
- Option to send via email to customer

### 3. Track Payments
- Mark invoices as paid
- View outstanding balances
- Generate monthly statements

## File Structure

```
logistics-app/
├── index.php              # Main entry point
├── config/
│   ├── config.php         # Application configuration
│   └── database.php       # Database connection
├── classes/
│   ├── LogisticsApp.php   # Main application class
│   ├── PDFGenerator.php   # PDF generation
│   └── EmailSender.php    # Email functionality
├── templates/
│   └── dashboard.php      # Dashboard template
├── assets/
│   ├── css/style.css      # Application styles
│   └── js/app.js          # JavaScript functionality
├── uploads/               # Generated PDFs and files
└── setup.sql             # Database schema and sample data
```

## Key Benefits

- **Lightweight** - No WordPress overhead
- **Simple Debugging** - Clear error messages and logging
- **Fast Performance** - Direct database queries
- **Easy Customization** - Clean, readable code
- **Mobile Responsive** - Works on all devices

## Troubleshooting

### Common Issues

1. **Database Connection Error**
   - Check database credentials in `config/database.php`
   - Ensure MySQL server is running
   - Verify database exists

2. **PDF Generation Issues**
   - Check `uploads/` directory permissions
   - Ensure PHP has write access

3. **Email Not Sending**
   - Configure SMTP settings in `EmailSender.php`
   - Check server email configuration

### Debug Mode

Enable debug mode by setting in `config/config.php`:
```php
ini_set('display_errors', 1);
error_reporting(E_ALL);
```

## Support

For issues or questions, check the browser console (F12) for JavaScript errors and PHP error logs for server-side issues.

## Learning

- [Build an LLM from scratch](LLM-FROM-SCRATCH.md)
