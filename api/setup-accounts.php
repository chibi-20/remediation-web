<?php
// setup-accounts.php - Create master admin and sample accounts
require_once '../config.php';

echo "<h2>🚀 Setting up TMS Accounts</h2>";

try {
    // 1. CREATE MASTER ADMIN ACCOUNT
    echo "<h3>1. Creating Master Admin Account</h3>";
    
    $masterUsername = '307901';
    $masterPassword = password_hash('ilovejacobo', PASSWORD_DEFAULT);
    
    // Check if master admin already exists
    $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
    $stmt->execute([$masterUsername]);
    
    if ($stmt->fetch()) {
        echo "⚠️ Master admin already exists. Updating password...<br>";
        $stmt = $pdo->prepare("UPDATE admins SET password = ? WHERE username = ?");
        $stmt->execute([$masterPassword, $masterUsername]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO admins (name, grade, subject, username, password) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            'Master Administrator',
            'All',
            'System Administration',
            $masterUsername,
            $masterPassword
        ]);
    }
    echo "✅ Master Admin Created: Username = $masterUsername, Password = ilovejacobo<br>";
    
    // 2. CREATE SAMPLE TEACHERS
    echo "<h3>2. Creating Sample Teacher Accounts</h3>";
    
    $teachers = [
        [
            'name' => 'Jay Mar V. Canturia',
            'grade' => '10',
            'subject' => 'Araling Panlipunan',
            'username' => 'jaymar.canturia',
            'password' => 'teacher123'
        ],
        [
            'name' => 'Rosemarie Canturia',
            'grade' => '10',
            'subject' => 'Mathematics',
            'username' => 'rose.canturia',
            'password' => 'teacher123'
        ],
        [
            'name' => 'Maria Santos',
            'grade' => '9',
            'subject' => 'English',
            'username' => 'maria.santos',
            'password' => 'teacher123'
        ]
    ];
    
    foreach ($teachers as $teacher) {
        // Check if teacher already exists
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
        $stmt->execute([$teacher['username']]);
        
        if (!$stmt->fetch()) {
            $hashedPassword = password_hash($teacher['password'], PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO admins (name, grade, subject, username, password) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([
                $teacher['name'],
                $teacher['grade'],
                $teacher['subject'],
                $teacher['username'],
                $hashedPassword
            ]);
            echo "✅ Teacher Created: {$teacher['name']} (Username: {$teacher['username']}, Password: {$teacher['password']})<br>";
        } else {
            echo "⚠️ Teacher {$teacher['name']} already exists<br>";
        }
    }
    
    // 3. CREATE SAMPLE STUDENTS
    echo "<h3>3. Creating Sample Student Accounts</h3>";
    
    $students = [
        [
            'firstName' => 'Juan',
            'lastName' => 'Dela Cruz',
            'section' => 'LEYNES',
            'lrn' => '123456789012',
            'password' => 'student123',
            'admin_id' => 2 // Jay Mar Canturia
        ],
        [
            'firstName' => 'Maria',
            'lastName' => 'Rodriguez',
            'section' => 'RIZAL',
            'lrn' => '123456789013',
            'password' => 'student123',
            'admin_id' => 3 // Rosemarie Canturia
        ],
        [
            'firstName' => 'Pedro',
            'lastName' => 'Garcia',
            'section' => 'BONIFACIO',
            'lrn' => '123456789014',
            'password' => 'student123',
            'admin_id' => 4 // Maria Santos
        ]
    ];
    
    foreach ($students as $student) {
        // Check if student already exists
        $stmt = $pdo->prepare("SELECT * FROM students WHERE lrn = ?");
        $stmt->execute([$student['lrn']]);
        
        if (!$stmt->fetch()) {
            $hashedPassword = password_hash($student['password'], PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO students (firstName, lastName, section, lrn, password, admin_id) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $student['firstName'],
                $student['lastName'],
                $student['section'],
                $student['lrn'],
                $hashedPassword,
                $student['admin_id']
            ]);
            echo "✅ Student Created: {$student['firstName']} {$student['lastName']} (LRN: {$student['lrn']}, Password: {$student['password']})<br>";
        } else {
            echo "⚠️ Student {$student['firstName']} {$student['lastName']} already exists<br>";
        }
    }
    
    // 4. SET UP TEACHER-SECTION RELATIONSHIPS
    echo "<h3>4. Setting up Teacher-Section Relationships</h3>";
    
    $relationships = [
        ['admin_id' => 2, 'section' => 'LEYNES', 'role' => 'adviser'],
        ['admin_id' => 3, 'section' => 'LEYNES', 'role' => 'subject_teacher'],
        ['admin_id' => 3, 'section' => 'RIZAL', 'role' => 'adviser'],
        ['admin_id' => 4, 'section' => 'BONIFACIO', 'role' => 'adviser'],
        ['admin_id' => 2, 'section' => 'RIZAL', 'role' => 'subject_teacher']
    ];
    
    foreach ($relationships as $rel) {
        $stmt = $pdo->prepare("INSERT IGNORE INTO teacher_sections (admin_id, section, role) VALUES (?, ?, ?)");
        $stmt->execute([$rel['admin_id'], $rel['section'], $rel['role']]);
        echo "✅ Added relationship: Admin {$rel['admin_id']} → Section {$rel['section']} as {$rel['role']}<br>";
    }
    
    echo "<h3>🎉 Account Setup Complete!</h3>";
    echo "<div style='background: #f0f9ff; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
    echo "<h4>📋 Login Credentials Summary:</h4>";
    echo "<strong>🔑 Master Admin:</strong><br>";
    echo "Username: <code>307901</code><br>";
    echo "Password: <code>ilovejacobo</code><br><br>";
    
    echo "<strong>👨‍🏫 Teachers:</strong><br>";
    echo "• jaymar.canturia / teacher123<br>";
    echo "• rose.canturia / teacher123<br>";
    echo "• maria.santos / teacher123<br><br>";
    
    echo "<strong>👨‍🎓 Students:</strong><br>";
    echo "• LRN: 123456789012 / student123 (Juan Dela Cruz - LEYNES)<br>";
    echo "• LRN: 123456789013 / student123 (Maria Rodriguez - RIZAL)<br>";
    echo "• LRN: 123456789014 / student123 (Pedro Garcia - BONIFACIO)<br>";
    echo "</div>";
    
    echo "<p><strong>Next Steps:</strong></p>";
    echo "<ol>";
    echo "<li>🏠 <a href='index.html'>Go to Home Page</a></li>";
    echo "<li>🔐 <a href='teacher-login.html'>Login as Admin/Teacher</a></li>";
    echo "<li>👥 <a href='teacher-sections.html'>Manage Teacher-Section Assignments</a></li>";
    echo "<li>📚 Create some modules and test the system!</li>";
    echo "</ol>";
    
} catch (PDOException $e) {
    echo "<div style='color: red;'>❌ Error: " . $e->getMessage() . "</div>";
}
?>
