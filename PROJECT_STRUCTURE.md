# Project Structure - PHP Version

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
