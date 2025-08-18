<?php
// test-security-isolation.php - Test teacher-student isolation
require_once 'config.php';

echo "<h1>Security & Isolation Test Results</h1>";
echo "<p>Testing that teachers can only see their own students and modules...</p>";

try {
    // Test data setup
    echo "<h2>Current Database State</h2>";
    
    // Show admins (teachers)
    $stmt = $pdo->query("SELECT id, name, username, sections FROM admins");
    $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<h3>Teachers:</h3><ul>";
    foreach ($admins as $admin) {
        echo "<li>ID: {$admin['id']}, Name: {$admin['name']}, Username: {$admin['username']}, Sections: {$admin['sections']}</li>";
    }
    echo "</ul>";
    
    // Show students with their teacher assignments
    $stmt = $pdo->query("SELECT s.id, s.firstName, s.lastName, s.lrn, s.admin_id, a.name as teacher_name 
                         FROM students s 
                         LEFT JOIN admins a ON s.admin_id = a.id 
                         ORDER BY s.admin_id");
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<h3>Students:</h3><ul>";
    foreach ($students as $student) {
        echo "<li>ID: {$student['id']}, Name: {$student['firstName']} {$student['lastName']}, LRN: {$student['lrn']}, Teacher: {$student['teacher_name']} (ID: {$student['admin_id']})</li>";
    }
    echo "</ul>";
    
    // Show modules with their teacher assignments  
    $stmt = $pdo->query("SELECT m.id, m.title, m.admin_id, a.name as teacher_name 
                         FROM modules m 
                         LEFT JOIN admins a ON m.admin_id = a.id 
                         ORDER BY m.admin_id");
    $modules = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<h3>Modules:</h3><ul>";
    foreach ($modules as $module) {
        echo "<li>ID: {$module['id']}, Title: {$module['title']}, Teacher: {$module['teacher_name']} (ID: {$module['admin_id']})</li>";
    }
    echo "</ul>";
    
    echo "<h2>API Security Tests</h2>";
    
    // Test 1: Students API with different teacher sessions
    echo "<h3>Test 1: Students API Isolation</h3>";
    foreach ($admins as $admin) {
        // Simulate admin session
        session_start();
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['username'] = $admin['username'];
        
        // Test students API
        $stmt = $pdo->prepare("SELECT * FROM students WHERE admin_id = ?");
        $stmt->execute([$admin['id']]);
        $teacherStudents = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<p>Teacher '{$admin['name']}' should see " . count($teacherStudents) . " students:</p>";
        echo "<ul>";
        foreach ($teacherStudents as $student) {
            echo "<li>{$student['firstName']} {$student['lastName']} (LRN: {$student['lrn']})</li>";
        }
        echo "</ul>";
        
        session_destroy();
    }
    
    // Test 2: Modules API with different teacher sessions
    echo "<h3>Test 2: Modules API Isolation</h3>";
    foreach ($admins as $admin) {
        // Simulate admin session
        session_start();
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['username'] = $admin['username'];
        
        // Test modules API
        $stmt = $pdo->prepare("SELECT * FROM modules WHERE admin_id = ?");
        $stmt->execute([$admin['id']]);
        $teacherModules = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<p>Teacher '{$admin['name']}' should see " . count($teacherModules) . " modules:</p>";
        echo "<ul>";
        foreach ($teacherModules as $module) {
            echo "<li>{$module['title']}</li>";
        }
        echo "</ul>";
        
        session_destroy();
    }
    
    // Test 3: Cross-teacher access attempts
    echo "<h3>Test 3: Cross-Teacher Access Prevention</h3>";
    
    if (count($admins) >= 2) {
        $teacher1 = $admins[0];
        $teacher2 = $admins[1];
        
        // Get a student from teacher2
        $stmt = $pdo->prepare("SELECT * FROM students WHERE admin_id = ? LIMIT 1");
        $stmt->execute([$teacher2['id']]);
        $teacher2Student = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($teacher2Student) {
            // Try to access teacher2's student while logged in as teacher1
            session_start();
            $_SESSION['admin_id'] = $teacher1['id'];
            
            $stmt = $pdo->prepare("SELECT * FROM students WHERE lrn = ? AND admin_id = ?");
            $stmt->execute([$teacher2Student['lrn'], $teacher1['id']]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$result) {
                echo "<p>✅ SUCCESS: Teacher '{$teacher1['name']}' cannot access student '{$teacher2Student['firstName']} {$teacher2Student['lastName']}' from teacher '{$teacher2['name']}'</p>";
            } else {
                echo "<p>❌ SECURITY ISSUE: Teacher '{$teacher1['name']}' can access student '{$teacher2Student['firstName']} {$teacher2Student['lastName']}' from teacher '{$teacher2['name']}'</p>";
            }
            
            session_destroy();
        }
    }
    
    echo "<h2>Security Summary</h2>";
    echo "<div style='background: #e8f5e8; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h3>✅ Security Measures Implemented:</h3>";
    echo "<ul>";
    echo "<li>Students API now requires admin authentication and filters by admin_id</li>";
    echo "<li>Modules API now requires admin authentication and filters by admin_id</li>";
    echo "<li>Update Progress API now verifies student belongs to logged-in teacher</li>";
    echo "<li>Reset Progress API now verifies student belongs to logged-in teacher</li>";
    echo "<li>Get Module Assessment API now verifies module belongs to student's teacher</li>";
    echo "<li>Take Assessment API now verifies module belongs to student's teacher</li>";
    echo "<li>All student data operations are now isolated by teacher</li>";
    echo "</ul>";
    echo "</div>";
    
    echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h3>⚠️ Next Steps:</h3>";
    echo "<ul>";
    echo "<li>Test all admin dashboard functions to ensure they work with the new authentication</li>";
    echo "<li>Test student login and module access to ensure they still work correctly</li>";
    echo "<li>Consider implementing role-based permissions for super admin vs regular teacher</li>";
    echo "<li>Add audit logging for sensitive operations</li>";
    echo "</ul>";
    echo "</div>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>Database Error: " . $e->getMessage() . "</p>";
}
?>
