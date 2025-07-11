======================================
        PROJECT: SAGIP ARAL
        Remediation Learning System
======================================

DESCRIPTION:
------------
Project SAGIP ARAL is a digital learning platform developed to support the remediation of students by enabling teachers to upload modules (in PDF format), create quizzes, and track student progress. This project is especially designed for use in schools like Jacobo Z. Gonzales Memorial National High School (JZGMNSH), led by Sir Jay Mar V. Canturia.

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
- Secure login for admins using hashed passwords (bcrypt)
- Each admin manages their own set of students and modules

📄 MODULE BUILDER:
- Input module title, quarter, and quiz questions
- Upload a PDF file as lesson material
- Students view both the module and quiz in a unified viewer page

📝 QUIZ SYSTEM:
- Supports multiple-choice format
- Automatically stores student answers and completion

📁 FILE STORAGE:
- PDF modules are uploaded and stored in the `public/uploads/` folder

💾 BACKEND:
- Built with Express.js
- Uses `better-sqlite3` for fast and reliable local database access
- Database contains tables for Admins, Students, Modules, Questions, and Quiz Results

🔧 FRONTEND:
- Fully responsive and easy to navigate
- Compatible with both desktop and mobile browsers

INSTALLATION:
-------------
1. Make sure you have Node.js installed on your system.
2. Clone this project or download the source code.
3. Navigate to the project directory in your terminal.
4. Run:
   npm install
5. Start the server:
   node server.js
6. Open your browser and go to:
   http://localhost:3000

FOLDER STRUCTURE:
-----------------
- /public
  - /uploads         -> Stores uploaded PDF files
  - /css             -> Contains stylesheets
  - /js              -> Contains frontend scripts
  - *.html           -> All client-facing pages

- /database
  - sagip-aral.db    -> SQLite database file (auto-created)

- server.js          -> Main Express backend

NOTES:
------
- Make sure uploaded PDF files are under the size limit (around 10MB for best performance).
- Admin accounts must be registered first before logging in.
- Each student must select a teacher (admin) during registration to access modules.

DEVELOPED BY:
-------------
Jay Mar V. Canturia
Teacher I, Araling Panlipunan Department
Jacobo Z. Gonzales Memorial National High School  
With the assistance of ChatGPT (OpenAI)

Last Updated: July 11, 2025

======================================
