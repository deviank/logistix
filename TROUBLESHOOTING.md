# Troubleshooting Guide - Can't Open Site

## Problem: Can't Access http://localhost/logistix

### Solution 1: Start Apache in XAMPP Control Panel

**Steps:**
1. **Open XAMPP Control Panel**
   - Press `Windows Key` and type "XAMPP"
   - Click on "XAMPP Control Panel"
   - Or navigate to: `C:\xampp\xampp-control.exe`

2. **Start Apache**
   - In XAMPP Control Panel, find "Apache" in the list
   - Click the **"Start"** button next to Apache
   - Wait for it to turn **green** (should say "Running")

3. **Verify Apache is Running**
   - The Apache row should show:
     - Status: **Running** (green)
     - Port: **80** (or another port if 80 is in use)

4. **Try Accessing Your Site Again**
   - Open browser: `http://localhost/logistix/`
   - Or: `http://localhost/logistix/index.php`

### Solution 2: Check Port Conflicts

If Apache won't start, port 80 might be in use:

1. **Check what's using port 80:**
   ```powershell
   netstat -ano | findstr ":80"
   ```

2. **Common port 80 conflicts:**
   - Skype (uses port 80)
   - IIS (Windows Web Server)
   - Other web servers

3. **Change Apache Port (if needed):**
   - In XAMPP Control Panel → Click "Config" next to Apache
   - Select "httpd.conf"
   - Find `Listen 80` and change to `Listen 8080`
   - Save and restart Apache
   - Access site at: `http://localhost:8080/logistix/`

### Solution 3: Check File Permissions

1. **Verify files exist:**
   - Check: `C:\xampp\htdocs\logistix\index.php` exists

2. **Check folder permissions:**
   - Right-click `C:\xampp\htdocs\logistix`
   - Properties → Security
   - Ensure "Users" or "Everyone" has Read & Execute permissions

### Solution 4: Test Basic Apache

1. **Test XAMPP default page:**
   - Go to: `http://localhost/`
   - Should see XAMPP welcome page
   - If this doesn't work, Apache isn't running

2. **Check Apache error logs:**
   - Location: `C:\xampp\apache\logs\error.log`
   - Look for recent errors

### Solution 5: Manual Apache Start

If XAMPP Control Panel doesn't work:

1. **Open Command Prompt as Administrator**
   - Right-click Start Menu → "Windows PowerShell (Admin)"

2. **Navigate to XAMPP:**
   ```powershell
   cd C:\xampp\apache\bin
   ```

3. **Start Apache:**
   ```powershell
   .\httpd.exe
   ```

### Common Error Messages

**"This site can't be reached" / "ERR_CONNECTION_REFUSED"**
- → Apache is not running
- → Start Apache in XAMPP Control Panel

**"404 Not Found"**
- → Files are in wrong location
- → Check: `C:\xampp\htdocs\logistix\index.php` exists
- → Try: `http://localhost/logistix/index.php`

**"Port 80 already in use"**
- → Another service is using port 80
- → Change Apache port or stop conflicting service

**"Access Denied"**
- → Run XAMPP Control Panel as Administrator
- → Right-click → "Run as administrator"

### Quick Test Commands

Open PowerShell and run:

```powershell
# Check if Apache is running
Get-Process | Where-Object {$_.ProcessName -eq "httpd"}

# Check port 80
netstat -ano | findstr ":80"

# Test if files exist
Test-Path "C:\xampp\htdocs\logistix\index.php"
```

### Still Not Working?

1. **Restart XAMPP:**
   - Stop Apache in XAMPP Control Panel
   - Close XAMPP Control Panel
   - Reopen XAMPP Control Panel
   - Start Apache again

2. **Check Windows Firewall:**
   - Windows Security → Firewall
   - Allow Apache through firewall if prompted

3. **Try different browser:**
   - Test in Chrome, Firefox, or Edge
   - Clear browser cache

4. **Check PHP is working:**
   - Create test file: `C:\xampp\htdocs\test.php`
   - Content: `<?php phpinfo(); ?>`
   - Access: `http://localhost/test.php`
   - Should show PHP information page

---

**Most Common Solution:** Start Apache in XAMPP Control Panel! 🚀

