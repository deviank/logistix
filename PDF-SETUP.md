# PDF Generation Setup - DomPDF

The application now uses DomPDF to generate actual PDF files (not just HTML) for statements.

## Installation

### Option 1: Using Composer (Recommended)

Run this command in your project directory:

```bash
composer install
```

This will install both PHPMailer and DomPDF.

### Option 2: Manual Installation

If Composer is having network issues, you can download DomPDF manually:

1. **Download DomPDF:**
   - Go to: https://github.com/dompdf/dompdf/releases
   - Download the latest release ZIP file

2. **Extract and Install:**
   - Extract the ZIP file
   - Copy the contents to: `logistics-app/vendor/dompdf/dompdf/`
   - Make sure the structure is: `vendor/dompdf/dompdf/src/Dompdf.php`

3. **Install Dependencies:**
   - DomPDF requires `phenx/php-font-lib` and `phenx/php-svg-lib`
   - Download from: https://github.com/phenx/php-font-lib and https://github.com/phenx/php-svg-lib
   - Place them in: `vendor/phenx/php-font-lib/` and `vendor/phenx/php-svg-lib/`

4. **Create autoload.php:**
   - Create `vendor/autoload.php` with basic autoloading, or use Composer's autoloader

**Note:** Manual installation is complex. It's much easier to use Composer or the PowerShell script below.

### Option 3: Using PowerShell Script

I can create a script to download DomPDF automatically. Let me know if you'd like that!

## After Installation

Once DomPDF is installed:

1. **Statements will be generated as PDF files** (`.pdf` extension)
2. **Emails will attach PDF files** instead of HTML files
3. **PDFs can be viewed, printed, and saved** by recipients

## Fallback Behavior

If DomPDF is not installed, the system will automatically fall back to HTML files, so the application will still work (just without PDF generation).

## Troubleshooting

### Error: "Class 'Dompdf\Dompdf' not found"
- DomPDF is not installed. Run `composer install` or install manually.

### PDF files are blank or malformed
- Check that DomPDF dependencies are installed correctly
- Ensure the `uploads/` directory is writable
- Check PHP error logs for specific errors

### PDF generation is slow
- This is normal for the first PDF generation
- DomPDF needs to process fonts and styles
- Subsequent generations should be faster

