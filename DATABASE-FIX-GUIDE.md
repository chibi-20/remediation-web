# 🔧 **CRITICAL FIX: Enable PHP MySQL Extensions**

## 🚨 **Problem Identified:**
- ✅ MySQL is running (port 3306 active)
- ❌ PHP MySQL extensions are NOT loaded
- Missing: `mysqli` and `pdo_mysql` extensions

## 🛠️ **Step-by-Step Fix:**

### **Step 1: Locate PHP Configuration File**
```bash
# Find your php.ini file location:
php --ini
```

### **Step 2: Edit php.ini File**
1. **Open XAMPP Control Panel**
2. **Click "Config" next to Apache**
3. **Select "PHP (php.ini)"**

OR manually open: `C:\xampp\php\php.ini`

### **Step 3: Enable MySQL Extensions**
Find these lines in php.ini and **REMOVE the semicolon (;)** at the beginning:

```ini
# Change from:
;extension=mysqli
;extension=pdo_mysql

# To:
extension=mysqli
extension=pdo_mysql
```

### **Step 4: Restart Apache**
1. **Open XAMPP Control Panel**
2. **Stop Apache** (if running)
3. **Start Apache** again

### **Step 5: Verify Fix**
1. **Run this command in terminal:**
   ```bash
   php -m | findstr mysql
   ```
   Should show: `mysqli`, `mysqlnd`, `pdo_mysql`

2. **Test via browser:**
   Visit: `http://localhost/tms/remediation-web/fix-database-connection.php`

## 🚀 **Quick Alternative: PowerShell Commands**

Run these commands in PowerShell as Administrator:

```powershell
# Navigate to XAMPP PHP directory
cd C:\xampp\php

# Backup original php.ini
copy php.ini php.ini.backup

# Enable extensions using PowerShell
(Get-Content php.ini) -replace ';extension=mysqli', 'extension=mysqli' | Set-Content php.ini
(Get-Content php.ini) -replace ';extension=pdo_mysql', 'extension=pdo_mysql' | Set-Content php.ini

# Restart Apache (if you have XAMPP in PATH)
# Or restart manually via XAMPP Control Panel
```

## ✅ **Verification Steps:**

### **1. Check PHP Extensions**
```bash
php -m | findstr mysql
```
Expected output:
```
mysqli
mysqlnd  
pdo_mysql
```

### **2. Test Database Connection**
Visit: `http://localhost/tms/remediation-web/fix-database-connection.php`

Should show:
- ✅ All extensions loaded
- ✅ MySQL connection successful
- ✅ Database access working

### **3. Test Your Application**
1. Visit: `http://localhost/tms/remediation-web/public/admin-register.html`
2. Register a test admin account
3. Login and test dashboard functionality

## 🎯 **After This Fix Works:**

1. **Test All APIs** - Verify students.php, modules.php work
2. **Test Security** - Verify teacher isolation
3. **Complete Testing** - Test full user flows  
4. **Production Setup** - Configure hosting environment

## ❓ **If This Doesn't Work:**

### **Alternative 1: Check XAMPP Version**
- Ensure you're using a recent XAMPP version
- PHP 7.4+ recommended

### **Alternative 2: Manual Extension Check**
```bash
# Check if extension files exist:
ls C:\xampp\php\ext\php_mysqli.dll
ls C:\xampp\php\ext\php_pdo_mysql.dll
```

### **Alternative 3: Use SQLite (Temporary)**
- Switch to SQLite for testing: `switch-database.php?action=sqlite`
- This bypasses MySQL requirement temporarily

---

## 🚨 **Action Required:**
**Please run the php.ini fix above and restart Apache, then test the database connection!**
