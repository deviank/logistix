# WAMP Setup Guide for Logistics Application

## Prerequisites

- WAMP Server installed and running
- Basic knowledge of file management
- Access to phpMyAdmin

## Step-by-Step Setup

### 1. Start WAMP Server

1. **Launch WAMP Server** from your desktop or start menu
2. **Wait for all services to start** (WAMP icon should be green)
3. **Verify services are running**:
   - Left-click WAMP icon → Check if Apache and MySQL are green
   - If not green, right-click WAMP icon → Start All Services

### 2. Copy Application Files

1. **Navigate to WAMP's www directory**:
   ```
   C:\wamp64\www\
   ```

2. **Create logistics-app folder**:
   ```
   C:\wamp64\www\logistics-app\
   ```

3. **Copy all application files** into the logistics-app folder:
   - All files from the logistics-app directory we created
   - Ensure the folder structure is maintained

### 3. Create Database

#### Option A: Using phpMyAdmin (Recommended)

1. **Open phpMyAdmin**:
   - Left-click WAMP icon → phpMyAdmin
   - Or go to: `http://localhost/phpmyadmin`

2. **Create Database**:
   - Click "New" in the left sidebar
   - Database name: `logistics_db`
   - Collation: `utf8mb4_unicode_ci`
   - Click "Create"

3. **Import Schema**:
   - Select the `logistics_db` database
   - Click "Import" tab
   - Click "Choose File" and select `setup.sql` from your logistics-app folder
   - Click "Go" to import

#### Option B: Using MySQL Command Line

1. **Open MySQL Console**:
   - Left-click WAMP icon → MySQL → MySQL Console
   - Enter password (usually empty for WAMP)

2. **Run Commands**:
   ```sql
   CREATE DATABASE logistics_db;
   USE logistics_db;
   SOURCE C:/wamp64/www/logistics-app/setup.sql;
   ```

### 4. Configure Database Connection

1. **Open** `config/database.php` in your text editor

2. **Update database credentials** (WAMP default settings):
   ```php
   private $host = 'localhost';
   private $db_name = 'logistics_db';
   private $username = 'root';
   private $password = '';  // Usually empty for WAMP
   ```

3. **Save the file**

### 5. Configure Application URL

1. **Open** `config/config.php` in your text editor

2. **Update the application URL**:
   ```php
   define('APP_URL', 'http://localhost/logistics-app');
   ```

3. **Save the file**

### 6. Set File Permissions

1. **Right-click** on the `uploads` folder in your logistics-app directory
2. **Properties** → **Security** tab
3. **Edit** → **Add** → **Everyone** → **Full Control**
4. **Apply** and **OK**

### 7. Test the Application

1. **Open your web browser**
2. **Navigate to**: `http://localhost/logistics-app`
3. **You should see the dashboard** with sample data

## Troubleshooting

### Common Issues and Solutions

#### 1. "Connection Error" Message

**Problem**: Database connection failed

**Solutions**:
- Check if MySQL is running (WAMP icon should be green)
- Verify database credentials in `config/database.php`
- Ensure database `logistics_db` exists
- Check if the `setup.sql` was imported correctly

#### 2. "Page Not Found" Error

**Problem**: Application not loading

**Solutions**:
- Verify files are in `C:\wamp64\www\logistics-app\`
- Check if Apache is running (WAMP icon should be green)
- Try: `http://localhost/logistics-app/index.php`

#### 3. "Permission Denied" for Uploads

**Problem**: Cannot create PDF files

**Solutions**:
- Set proper permissions on `uploads` folder
- Ensure `uploads` folder exists
- Check Windows file permissions

#### 4. Sample Data Not Showing

**Problem**: Dashboard shows "No data found"

**Solutions**:
- Verify `setup.sql` was imported completely
- Check if sample data exists in database:
  ```sql
  SELECT * FROM companies;
  SELECT * FROM load_sheets;
  SELECT * FROM invoices;
  ```

### 5. Email Not Working

**Problem**: Invoice emails not sending

**Solutions**:
- WAMP doesn't have SMTP configured by default
- For testing, emails will appear in WAMP's mail log
- For production, configure SMTP in `classes/EmailSender.php`

## Testing the Workflow

### 1. View Sample Data

1. **Dashboard** should show:
   - 3 Active Companies
   - Recent Load Sheets
   - Pending Invoices
   - Statistics

### 2. Test Invoice Creation

1. **Click "Create Invoice"** on a completed load sheet
2. **Should see**:
   - Success message with invoice number
   - PDF link to view invoice
   - Email dialog to send to customer

### 3. Test PDF Generation

1. **Click the PDF link** after creating an invoice
2. **Should open** a professional invoice in browser
3. **Can print** or save as PDF

### 4. Test Email Dialog

1. **Enter email address** in the dialog
2. **Click "Send Invoice"**
3. **Should see** success message (email won't actually send in WAMP)

## File Structure Verification

Your `C:\wamp64\www\logistics-app\` should contain:

```
logistics-app/
├── index.php
├── config/
│   ├── config.php
│   └── database.php
├── classes/
│   ├── LogisticsApp.php
│   ├── PDFGenerator.php
│   └── EmailSender.php
├── templates/
│   └── dashboard.php
├── assets/
│   ├── css/style.css
│   └── js/app.js
├── uploads/ (empty initially)
├── setup.sql
├── README.md
└── WAMP-SETUP-GUIDE.md
```

## Next Steps

1. **Test all functionality** with sample data
2. **Customize company details** in the database
3. **Add your own company information** in the PDF templates
4. **Configure email settings** for production use
5. **Backup your database** regularly

## Production Considerations

When moving to production:

1. **Change database password** from empty
2. **Configure proper SMTP** for email sending
3. **Set up SSL certificate** for HTTPS
4. **Configure proper file permissions**
5. **Set up regular database backups**

## Support

If you encounter issues:

1. **Check WAMP error logs**:
   - Apache: `C:\wamp64\logs\apache_error.log`
   - PHP: `C:\wamp64\logs\php_error.log`

2. **Check browser console** (F12) for JavaScript errors

3. **Verify database** using phpMyAdmin

4. **Test database connection** by creating a simple PHP file:
   ```php
   <?php
   require_once 'config/database.php';
   $db = new Database();
   $conn = $db->getConnection();
   if ($conn) {
       echo "Database connected successfully!";
   } else {
       echo "Database connection failed!";
   }
   ?>
   ```

The application should now be running smoothly on your WAMP server!
