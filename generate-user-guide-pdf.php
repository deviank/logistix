<?php
/**
 * Generate User Guide PDF
 * Run this in your browser: http://localhost/logistics-app/generate-user-guide-pdf.php
 * The PDF will be saved to uploads/User-Guide.pdf and you can download it from there.
 */

require_once 'config/config.php';
require_once 'vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

$html = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>User Guide - Logistics System</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            font-size: 11px;
            line-height: 1.5;
            color: #333;
            padding: 30px;
            max-width: 700px;
            margin: 0 auto;
        }
        h1 {
            font-size: 22px;
            color: #007cba;
            margin-bottom: 5px;
            border-bottom: 2px solid #007cba;
            padding-bottom: 8px;
        }
        h2 {
            font-size: 15px;
            color: #333;
            margin: 20px 0 10px 0;
            font-weight: 600;
        }
        h3 {
            font-size: 12px;
            color: #555;
            margin: 14px 0 6px 0;
            font-weight: 600;
        }
        p { margin-bottom: 8px; }
        ul, ol { margin: 8px 0 8px 25px; }
        li { margin-bottom: 4px; }
        strong { font-weight: 600; }
        hr {
            border: none;
            border-top: 1px solid #ddd;
            margin: 18px 0;
        }
        .subtitle {
            font-size: 13px;
            color: #666;
            margin-bottom: 20px;
        }
        .step-num {
            display: inline-block;
            background: #007cba;
            color: white;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 10px;
            margin-right: 5px;
        }
        .tip { font-style: italic; color: #555; margin-top: 15px; }
    </style>
</head>
<body>

<h1>How to Use the Logistics System</h1>
<p class="subtitle">Super Simple Guide — No Experience Needed</p>

<hr>

<h2>Step 1: Open the System</h2>
<ol>
    <li>Open your internet browser. (Chrome, Edge, or Firefox.)</li>
    <li>Look at the top of the browser. You will see a long white box. That is called the address bar.</li>
    <li>Click inside that box.</li>
    <li>Type: <strong>your web address</strong> (you will be given this when the system is ready)</li>
    <li>Press Enter on your keyboard.</li>
    <li>The system will open. You should see a page that says "Dashboard" at the top.</li>
</ol>

<hr>

<h2>Step 2: What is the Dashboard?</h2>
<p>The Dashboard is the first page you see. It shows you 3 numbers:</p>
<ul>
    <li><strong>Invoices This Month</strong> — How many bills you sent this month.</li>
    <li><strong>Outstanding Balance</strong> — How much money people still owe you. (In Rands.)</li>
    <li><strong>Active Companies</strong> — How many customers you have.</li>
</ul>
<p>At the bottom you will see 2 buttons:</p>
<ul>
    <li><strong>New Load Sheet</strong> — Click this when you want to make a new delivery job.</li>
    <li><strong>New Company</strong> — Click this when you want to add a new customer.</li>
</ul>

<hr>

<h2>Step 3: Adding a Customer (Company)</h2>
<p>A "Company" is just a customer. The people who pay you to move their stuff.</p>
<p><strong>You MUST add a company before you can do anything else.</strong></p>

<h3>How to add one:</h3>
<ol>
    <li>Click <strong>Companies</strong> in the menu at the top.</li>
    <li>Click the <strong>Add New Company</strong> button.</li>
    <li>A box will pop up with empty spaces to fill.</li>
    <li>Fill in each space:
        <ul>
            <li><strong>Company Name</strong> — The name of the business. (Example: Spar Store)</li>
            <li><strong>Contact Person</strong> — The person you talk to. (Example: John)</li>
            <li><strong>Email</strong> — Their email address.</li>
            <li><strong>Phone</strong> — Their phone number.</li>
            <li><strong>Address</strong> — Where they are. (You can leave this blank if you want.)</li>
            <li><strong>Rate per Pallet</strong> — How much you charge for one pallet. Type a number like 500.</li>
            <li><strong>Payment Terms</strong> — How many days they have to pay. Type 30 (meaning 30 days).</li>
        </ul>
    </li>
    <li>Click <strong>Save Company</strong>.</li>
    <li>Done! You will see the new company on the page.</li>
</ol>

<h3>Other things you can do:</h3>
<ul>
    <li><strong>New Load Sheet</strong> — Make a delivery job for this company.</li>
    <li><strong>Edit</strong> — Change their details.</li>
    <li><strong>Details</strong> — Look at their info.</li>
    <li><strong>Deactivate</strong> — Turn them off if they are no longer a customer.</li>
</ul>

<h3>How to find a company:</h3>
<ul>
    <li>Type in the box that says "Search companies..."</li>
    <li>The list will get smaller as you type. Only matching companies will show.</li>
</ul>

<hr>

<h2>Step 4: Making a Load Sheet (Delivery Job)</h2>
<p>A Load Sheet is one delivery job. It tells you: who, what, when, and how much.</p>

<h3>How to make one:</h3>
<p><strong>Way 1:</strong></p>
<ol>
    <li>Click <strong>Load Sheets</strong> in the menu.</li>
    <li>Click <strong>New Load Sheet</strong>.</li>
</ol>
<p><strong>Way 2:</strong></p>
<ol>
    <li>Go to <strong>Companies</strong>.</li>
    <li>Find the company.</li>
    <li>Click <strong>New Load Sheet</strong> on their card.</li>
</ol>

<h3>What to type in each box:</h3>
<ul>
    <li><strong>Company</strong> — Pick the customer from the list. (Click the arrow and choose one.)</li>
    <li><strong>Date</strong> — When is the delivery? Pick a date.</li>
    <li><strong>Pallet Quantity</strong> — How many pallets? Type a number like 5.</li>
    <li><strong>Rate per Pallet</strong> — How much per pallet? Type a number. (Often this is already filled in.)</li>
    <li><strong>Cargo Description</strong> — What are you moving? Type words like "groceries" or "furniture".</li>
    <li><strong>Delivery Method</strong> — Pick one:
        <ul>
            <li><strong>Own Driver</strong> — Your own people deliver it.</li>
            <li><strong>Contractor</strong> — Someone else delivers it for you.</li>
        </ul>
    </li>
    <li><strong>Status</strong> — Pick one:
        <ul>
            <li><strong>Pending</strong> — Not started yet.</li>
            <li><strong>In Progress</strong> — Doing it now.</li>
            <li><strong>Completed</strong> — All done!</li>
        </ul>
    </li>
</ul>
<p>If you picked "Contractor" you will also see:</p>
<ul>
    <li><strong>Contractor</strong> — Pick who will do it. (Or click "+ Add New" to add someone new.)</li>
    <li><strong>Contractor Cost</strong> — How much do you pay them? Type a number.</li>
</ul>
<p>5. Click <strong>Save Load Sheet</strong>.</p>
<p>6. Done!</p>

<h3>After you save:</h3>
<ul>
    <li>Click <strong>View</strong> to see the details.</li>
    <li>Click <strong>Edit</strong> to change something. (Only works if it is not Completed yet.)</li>
    <li>When the job is <strong>Completed</strong>, a <strong>Create Invoice</strong> button will appear. Click it to make a bill!</li>
</ul>

<h3>How to find a load sheet:</h3>
<ul>
    <li>Use the Status box to filter. (Show only In Progress, or only Completed.)</li>
    <li>Type in the Search box to find one.</li>
</ul>

<hr>

<h2>Step 5: Invoices (The Bill You Send)</h2>
<p>An Invoice is the bill. You send it to the customer so they know how much to pay.</p>

<h3>How does an invoice get made?</h3>
<ol>
    <li>First, the Load Sheet must be <strong>Completed</strong>.</li>
    <li>Then you click <strong>Create Invoice</strong> on that load sheet.</li>
    <li>The system makes it for you. It adds tax (VAT) and works out the total. You don't have to do any maths.</li>
</ol>

<h3>What you can do with an invoice:</h3>
<ul>
    <li><strong>View</strong> — See everything (company, amounts, dates).</li>
    <li><strong>PDF</strong> — Get a file you can print or send.</li>
    <li><strong>Mark Paid</strong> — The customer paid? Click this. It changes the status to Paid.</li>
    <li><strong>Send Email</strong> — Send the bill to their email.</li>
    <li><strong>Statement</strong> — Make a monthly summary that includes this invoice.</li>
</ul>

<h3>What do the colours/statuses mean?</h3>
<ul>
    <li><strong>Pending</strong> — Waiting for money.</li>
    <li><strong>Paid</strong> — They paid!</li>
    <li><strong>Overdue</strong> — They are late. (Shows in red.)</li>
</ul>

<h3>How to filter invoices:</h3>
<p>Use the dropdown boxes at the top. You can filter by Status, Date, or Company.</p>

<hr>

<h2>Step 6: Statements (Monthly Summary)</h2>
<p>A Statement is a list of all the bills for one customer in one month. Like a mini report.</p>

<h3>How to make one:</h3>
<ol>
    <li>Click <strong>Statements</strong> in the menu.</li>
    <li>Click <strong>Generate New Statement</strong>.</li>
    <li>Pick the <strong>Company</strong>.</li>
    <li>Pick the <strong>Month</strong>.</li>
    <li>Click <strong>Generate Statement</strong>.</li>
    <li>A PDF is made. You can download it or email it.</li>
</ol>

<h3>What you can do with a statement:</h3>
<ul>
    <li><strong>View</strong> — See the details.</li>
    <li><strong>PDF</strong> — Download it.</li>
    <li><strong>Send Email</strong> — Email it to the customer.</li>
</ul>

<hr>

<h2>Step 7: Contractors (Outside Drivers)</h2>
<p>A Contractor is someone who delivers for you when you don't use your own drivers.</p>

<h3>How to add one:</h3>
<ol>
    <li>You are making a Load Sheet.</li>
    <li>Under Delivery Method, pick <strong>Contractor</strong>.</li>
    <li>A new box appears. Click <strong>+ Add New</strong>.</li>
    <li>Fill in their name, phone, and email.</li>
    <li>Click <strong>Add Contractor</strong>.</li>
    <li>Now you can pick them from the list next time.</li>
</ol>

<hr>

<h2>The Order of Things (Do This First, Then That)</h2>
<ol>
    <li><strong>Add a Company</strong> (your customer) — Start here!</li>
    <li><strong>Make a Load Sheet</strong> (the delivery job)</li>
    <li><strong>Set Status to Completed</strong> when the job is done</li>
    <li><strong>Click Create Invoice</strong> (the system makes the bill)</li>
    <li><strong>Send the Invoice</strong> (PDF or email)</li>
    <li><strong>Click Mark Paid</strong> when they pay you</li>
    <li><strong>Generate a Statement</strong> at the end of the month (optional)</li>
</ol>

<hr>

<h2>Stuck?</h2>
<ul>
    <li><strong>Nothing on the screen?</strong> Add a Company first. You need at least one.</li>
    <li><strong>No "Create Invoice" button?</strong> The Load Sheet must be <strong>Completed</strong>.</li>
    <li><strong>Can't find something?</strong> Use the Search box.</li>
    <li><strong>More help?</strong> Look at the TROUBLESHOOTING.md file.</li>
</ul>

<p class="tip">Go step by step. You can do it!</p>

</body>
</html>
HTML;

// Generate PDF
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', false);

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// Save to uploads folder
$filename = 'User-Guide.pdf';
$filepath = rtrim(UPLOADS_PATH, '/\\') . DIRECTORY_SEPARATOR . $filename;
file_put_contents($filepath, $dompdf->output());

// Offer download or show success
$download = isset($_GET['download']) && $_GET['download'] === '1';

if ($download) {
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . filesize($filepath));
    readfile($filepath);
    exit;
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>User Guide PDF Generated</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 500px; margin: 50px auto; padding: 30px; text-align: center; background: #f5f5f5; }
        .box { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #28a745; font-size: 24px; margin-bottom: 15px; }
        p { color: #555; margin-bottom: 20px; }
        a { display: inline-block; padding: 12px 24px; background: #007cba; color: white; text-decoration: none; border-radius: 4px; }
        a:hover { background: #005a87; }
    </style>
</head>
<body>
    <div class="box">
        <h1>PDF Ready!</h1>
        <p>The User Guide has been saved as a PDF.</p>
        <p><strong>File location:</strong><br>uploads/User-Guide.pdf</p>
        <p><a href="generate-user-guide-pdf.php?download=1">Download PDF</a></p>
        <p style="margin-top: 20px; font-size: 14px;"><a href="index.php" style="background: #6c757d;">Back to Dashboard</a></p>
    </div>
</body>
</html>
