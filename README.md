======================================
        PROJECT: SAGIP ARAL
        Remediation Learning System (PHP Version)
======================================

DESCRIPTION:
------------
Project SAGIP ARAL is a digital learning platform developed to support the remediation of students by enabling teachers to upload modules (in PDF format), create quizzes, and track student progress. This project has been converted from Node.js to PHP for better compatibility with standard web hosting environments and XAMPP development setups.

FEATURES:
---------
🧑‍🏫 ADMIN/TEACHER FEATURES:
- Admin Registration and Login System
- Module Creation (with PDF upload and quiz builder)
- Edit/Delete Modules
- View Student Progress per Module
- Assign Students to Their Account

👨‍🎓 STUDENT FEATURES:
- Student Registration (select assigned teacher)
- View Assigned Modules
- Open PDF Modules in Fullscreen Viewer
- Answer Quizzes Related to Modules
- Track Completed and Unfinished Modules

🔒 AUTHENTICATION:
- Secure login for admins using hashed passwords (PHP password_hash)
- Session management for maintaining login state
- Each admin manages their own set of students and modules

📄 MODULE BUILDER:
- Input module title, quarter, and quiz questions
- Upload a PDF file as lesson material
- Students view both the module and quiz in a unified viewer page

📝 QUIZ SYSTEM:
- Supports multiple-choice format
- Automatically stores student answers and completion

📁 FILE STORAGE:
- PDF modules are uploaded and stored in the `public/modules/` folder

💾 BACKEND:
- Built with PHP 7.4+
- Uses PDO SQLite for fast and reliable local database access
- Database contains tables for Admins, Students, and Modules
- RESTful API endpoints for all operations

🔧 FRONTEND:
- Fully responsive and easy to navigate
- Compatible with both desktop and mobile browsers
- Same user interface as the original Node.js version

INSTALLATION:
-------------

### FOR XAMPP (RECOMMENDED):
1. Make sure you have XAMPP installed on your system
2. Copy this project folder to: C:\xampp\htdocs\tms\remediation-web
3. Start Apache in XAMPP Control Panel
4. Open your browser and go to: http://localhost/tms/remediation-web

### FOR BUILT-IN PHP SERVER:
1. Make sure you have PHP 7.4+ installed on your system
2. Navigate to the project directory in your terminal
3. Run: php -S localhost:8000
4. Open your browser and go to: http://localhost:8000

### FOR PRODUCTION SERVER:
1. Upload all files to your web server
2. Ensure PHP 7.4+ and PDO SQLite extension are available
3. Set proper file permissions for database and modules directory
4. Configure web server to route requests through index.php

FOLDER STRUCTURE:
-----------------
- /api                     -> PHP API endpoints
  - admin-login.php        -> Admin authentication
  - register-admin.php     -> Admin registration
  - students.php           -> Student data management
  - modules.php            -> Module listing
  - create-module.php      -> Module creation
  - update-module.php      -> Module editing
  - update-progress.php    -> Progress tracking

- /public
  - /modules               -> Stores uploaded PDF files
  - /css                   -> Contains stylesheets
  - /js                    -> Contains frontend scripts
  - *.html                 -> All client-facing pages

- index.php                -> Main entry point and router
- config.php               -> Database configuration and utilities
- composer.json            -> PHP dependencies
- .htaccess               -> Apache configuration
- students.db             -> SQLite database file (auto-created)

MIGRATION FROM NODE.JS:
-----------------------
This project has been converted from Node.js to PHP with the following changes:
- Replaced Express.js server with PHP and Apache/Nginx
- Converted all API routes to individual PHP files
- Maintained the same SQLite database schema using PDO
- Added proper routing through index.php and .htaccess
- Kept all frontend HTML/CSS/JavaScript files unchanged
- Updated authentication to use PHP sessions instead of JWT

REQUIREMENTS:
-------------
- PHP 7.4 or higher
- PDO SQLite extension
- Apache/Nginx web server (or PHP built-in server for development)
- mod_rewrite enabled (for Apache clean URLs)

NOTES:
------
- Make sure uploaded PDF files are under the size limit (around 10MB for best performance)
- Admin accounts must be registered first before logging in
- Each student must select a teacher (admin) during registration to access modules
- The SQLite database file will be created automatically on first run
- All frontend functionality remains the same as the original Node.js version

DEVELOPED BY:
-------------
Jay Mar V. Canturia
Teacher I, Araling Panlipunan Department
Jacobo Z. Gonzales Memorial National High School  
Converted to PHP version with the assistance of GitHub Copilot

Last Updated: August 12, 2025

======================================
