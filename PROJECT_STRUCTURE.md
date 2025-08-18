# Remediation Web - Project Structure

```
remediation-web/
│
├── api/                        # Backend API endpoints
│   ├── admin-login.php         # Admin authentication
│   ├── admins.php              # Admin management
│   ├── create-module.php       # Module creation with PDF upload
│   ├── debug-data.php          # Debug data operations
│   ├── fix-student.php         # Student data fixes
│   ├── get-module-assessment.php # Get assessment questions
│   ├── logout.php              # Session logout
│   ├── modules.php             # Module data management
│   ├── register-admin.php      # Admin registration
│   ├── register-student.php    # Student registration
│   ├── reset-progress.php      # Reset student progress
│   ├── router.php              # API routing
│   ├── student-login.php       # Student authentication
│   ├── students.php            # Student data management
│   ├── take-assessment.php     # Process assessment submissions
│   ├── update-module.php       # Module updates
│   └── update-progress.php     # Update student progress
│
├── public/                     # Frontend web pages
│   ├── css/
│   │   └── style.css          # Custom styles
│   ├── js/
│   │   ├── script.js          # Main JavaScript
│   │   ├── script-edit-module.js # Module editing functionality
│   │   └── script-module-creator.js # Module creation functionality
│   ├── modules/               # Uploaded PDF modules
│   │   ├── *.pdf             # Teacher uploaded learning modules
│   │   └── module1.html      # Sample module
│   ├── admin.html            # Admin dashboard
│   ├── admin-login.html      # Admin login page
│   ├── admin-module-creator.html # Module creation interface
│   ├── admin-register.html   # Admin registration page
│   ├── dashboard.html        # Student dashboard
│   ├── edit-module.html      # Module editing interface
│   ├── login.html           # Student login page
│   ├── module-assessment.html # Assessment taking interface
│   ├── module-viewer.html    # Module viewing interface
│   └── register.html        # Student registration page
│
├── debug/                      # 🆕 Development & testing tools
│   ├── api-fix-summary.php    # API fixes documentation
│   ├── auto-fix-php-extensions.php # Auto-fix PHP MySQL extensions
│   ├── check-database.php     # Database connectivity check
│   ├── check-php.php          # PHP environment check
│   ├── check-sqlite.php       # SQLite database inspector
│   ├── debug.php              # General debugging
│   ├── debug-dashboard.php    # Dashboard debugging
│   ├── debug-routes.php       # API routes debugging
│   ├── direct-test.php        # Direct system testing
│   ├── fix-database-connection.php # Database connection diagnostics
│   ├── index.php              # 🆕 Debug tools dashboard
│   ├── migrate-db.php         # Database migration
│   ├── migrate-teachers-db.php # Teacher data migration
│   ├── migrate.php            # General migration
│   ├── mysql-status.php       # MySQL server status
│   ├── phpinfo.php            # PHP configuration info
│   ├── prepare-for-production.php # Production readiness check
│   ├── simple-admins-test.php # Basic admin functionality test
│   ├── test.php               # General testing
│   ├── test-admins-api.php    # Admin API testing
│   ├── test-output.html       # Test results output
│   ├── test-routes.php        # API routes testing
│   ├── test-security-isolation.php # Security isolation testing
│   └── update-admins-table.php # Admin table updates
│
├── .htaccess                   # Apache configuration
├── composer.json               # PHP dependencies
├── config.php                  # Main database configuration
├── config-mysql.php           # MySQL configuration template
├── DATABASE-FIX-GUIDE.md      # Database setup guide
├── index.php                   # Main application entry point
├── PRODUCTION-READINESS.md    # Production deployment guide
├── PROJECT_STRUCTURE.md       # This file
├── README.md                   # Project documentation
├── SECURITY-FIXES.md          # Security improvements documentation
├── SECURITY.md                # Security guidelines
├── students.db                # Legacy SQLite database file
└── switch-database.php        # Database configuration switcher
```

## 🎯 **Clean Root Directory**

The root directory now contains only essential files:
- **Core Files**: `index.php`, `config.php`, etc.
- **Documentation**: `README.md`, `SECURITY.md`, etc.
- **Main Folders**: `api/`, `public/`, `debug/`

## 🛠️ **Debug Tools Organization**

All development, testing, and debugging tools are now organized in the `debug/` folder:
- **Access**: Visit `/debug/` for a organized dashboard
- **Production**: Remove or secure this folder before deployment
- **Development**: Use these tools for troubleshooting and testing

## 📁 **Key Directories**

- **`api/`** - Backend REST API endpoints
- **`public/`** - Frontend user interface
- **`debug/`** - Development & testing tools (remove for production)

## 🚀 **Benefits of New Structure**

✅ **Clean Root** - Only essential files visible  
✅ **Organized Tools** - All debug/test files in one place  
✅ **Easy Maintenance** - Clear separation of concerns  
✅ **Production Ready** - Simple to remove debug folder  
✅ **Developer Friendly** - Easy access to all development tools

## Clean Directory Structure
```
remediation-web/
├── .htaccess                 # Apache configuration for clean URLs
├── composer.json             # PHP dependencies and scripts
├── config.php               # Database configuration and utilities
├── index.php                # Main entry point and router
├── migrate.php              # Migration summary page
├── test.php                 # System test script
├── README.md                # Project documentation
├── SECURITY.md              # Security information
├── students.db              # SQLite database (auto-created)
├── api/                     # PHP API endpoints
│   ├── admin-login.php      # Admin authentication
│   ├── register-admin.php   # Admin registration
│   ├── student-login.php    # Student authentication
│   ├── register-student.php # Student registration
│   ├── students.php         # Student data management
│   ├── modules.php          # Module listing
│   ├── create-module.php    # Module creation
│   ├── update-module.php    # Module editing
│   ├── update-progress.php  # Progress tracking
│   └── logout.php           # Logout functionality
└── public/                  # Static frontend files
    ├── admin.html           # Admin dashboard
    ├── admin-login.html     # Admin login page
    ├── admin-register.html  # Admin registration page
    ├── admin-module-creator.html # Module creation page
    ├── dashboard.html       # Student dashboard
    ├── edit-module.html     # Module editing page
    ├── login.html           # Student login page
    ├── module-viewer.html   # Module viewing page
    ├── register.html        # Student registration page
    ├── css/                 # Stylesheets
    │   └── style.css
    ├── js/                  # JavaScript files
    │   ├── script.js
    │   ├── script-module-creator.js
    │   └── script-edit-module.js
    └── modules/             # Uploaded PDF modules
        ├── 1751846970033-AP10_q1_mod2_HamongPangkapaligiran_v3.pdf
        ├── 1752193269481-AP10_q1_mod3_PagharapSaSuliraningKapaligiran_v3.pdf
        └── module1.html
```

## Removed Node.js Files
- ✅ server.js (deleted)
- ✅ package.json (deleted) 
- ✅ package-lock.json (deleted)
- ✅ database.js (deleted)
- ✅ node_modules/ (deleted)

## Technology Stack
- **Backend**: PHP 7.4+ with PDO SQLite
- **Frontend**: HTML5, CSS3, JavaScript (ES6)
- **Database**: SQLite 3
- **Web Server**: Apache (with mod_rewrite) or PHP built-in server
- **Authentication**: PHP sessions with password hashing

## Quick Start
1. Ensure Apache is running in XAMPP
2. Visit: http://localhost/tms/remediation-web
3. Or use PHP server: `php -S localhost:8000`
