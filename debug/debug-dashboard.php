<?php
// debug-dashboard.php - Debug student dashboard data
require_once 'config.php';

echo "<h1>Student Dashboard Debug</h1>";

// Test students API
echo "<h2>Students API Test</h2>";
try {
    $stmt = $pdo->prepare("SELECT * FROM students");
    $stmt->execute();
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($students)) {
        echo "<p style='color: orange;'>⚠️ No students found in database</p>";
        echo "<p><a href='public/register.html'>Register a student first</a></p>";
    } else {
        echo "<p style='color: green;'>✅ Found " . count($students) . " student(s):</p>";
        foreach ($students as $student) {
            echo "<div style='border: 1px solid #ccc; margin: 10px; padding: 10px;'>";
            echo "<p><strong>ID:</strong> {$student['id']}</p>";
            echo "<p><strong>Name:</strong> {$student['firstName']} {$student['lastName']}</p>";
            echo "<p><strong>LRN:</strong> {$student['lrn']}</p>";
            echo "<p><strong>Admin ID:</strong> {$student['admin_id']}</p>";
            echo "<p><strong>Progress:</strong> " . htmlspecialchars($student['progress']) . "</p>";
            echo "</div>";
        }
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Students API Error: " . $e->getMessage() . "</p>";
}

// Test modules API
echo "<h2>Modules API Test</h2>";
try {
    $stmt = $pdo->prepare("SELECT * FROM modules");
    $stmt->execute();
    $modules = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($modules)) {
        echo "<p style='color: orange;'>⚠️ No modules found in database</p>";
        echo "<p>Admins need to create modules first</p>";
        echo "<p><a href='public/admin-module-creator.html'>Create a module (admin required)</a></p>";
    } else {
        echo "<p style='color: green;'>✅ Found " . count($modules) . " module(s):</p>";
        foreach ($modules as $module) {
            echo "<div style='border: 1px solid #ccc; margin: 10px; padding: 10px;'>";
            echo "<p><strong>ID:</strong> {$module['id']}</p>";
            echo "<p><strong>Quarter:</strong> {$module['quarter']}</p>";
            echo "<p><strong>Filename:</strong> {$module['filename']}</p>";
            echo "<p><strong>Admin ID:</strong> {$module['admin_id']}</p>";
            echo "<p><strong>Questions:</strong> " . htmlspecialchars(substr($module['questions'], 0, 100)) . "...</p>";
            echo "</div>";
        }
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Modules API Error: " . $e->getMessage() . "</p>";
}

// Test admin-student relationships
echo "<h2>Admin-Student Relationship Test</h2>";
try {
    $stmt = $pdo->query("
        SELECT 
            s.id as student_id,
            s.firstName,
            s.lastName,
            s.lrn,
            s.admin_id,
            a.username as admin_username,
            a.name as admin_name
        FROM students s
        LEFT JOIN admins a ON s.admin_id = a.id
    ");
    $relationships = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($relationships)) {
        echo "<p style='color: orange;'>⚠️ No student-admin relationships found</p>";
    } else {
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
        echo "<tr><th>Student</th><th>LRN</th><th>Admin ID</th><th>Admin Name</th></tr>";
        foreach ($relationships as $rel) {
            echo "<tr>";
            echo "<td>{$rel['firstName']} {$rel['lastName']}</td>";
            echo "<td>{$rel['lrn']}</td>";
            echo "<td>{$rel['admin_id']}</td>";
            echo "<td>{$rel['admin_name']} ({$rel['admin_username']})</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Relationship query error: " . $e->getMessage() . "</p>";
}

// Test module-admin relationships
echo "<h2>Module-Admin Relationship Test</h2>";
try {
    $stmt = $pdo->query("
        SELECT 
            m.id as module_id,
            m.quarter,
            m.filename,
            m.admin_id,
            a.username as admin_username,
            a.name as admin_name
        FROM modules m
        LEFT JOIN admins a ON m.admin_id = a.id
    ");
    $moduleRels = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($moduleRels)) {
        echo "<p style='color: orange;'>⚠️ No module-admin relationships found</p>";
    } else {
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
        echo "<tr><th>Module ID</th><th>Quarter</th><th>Filename</th><th>Admin ID</th><th>Admin Name</th></tr>";
        foreach ($moduleRels as $rel) {
            echo "<tr>";
            echo "<td>{$rel['module_id']}</td>";
            echo "<td>{$rel['quarter']}</td>";
            echo "<td>{$rel['filename']}</td>";
            echo "<td>{$rel['admin_id']}</td>";
            echo "<td>{$rel['admin_name']} ({$rel['admin_username']})</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Module relationship query error: " . $e->getMessage() . "</p>";
}

echo "<h2>Possible Issues & Solutions</h2>";
echo "<ul>";
echo "<li><strong>No modules:</strong> Admin needs to create modules first</li>";
echo "<li><strong>No students:</strong> Students need to register first</li>";
echo "<li><strong>Admin ID mismatch:</strong> Student's admin_id doesn't match module's admin_id</li>";
echo "<li><strong>API errors:</strong> Check database connection and file permissions</li>";
echo "</ul>";

echo "<h2>Quick Links</h2>";
echo "<ul>";
echo "<li><a href='public/admin-login.html'>Admin Login</a> (to create modules)</li>";
echo "<li><a href='public/register.html'>Student Registration</a></li>";
echo "<li><a href='public/dashboard.html'>Student Dashboard</a></li>";
echo "<li><a href='api/students.php'>Test Students API</a></li>";
echo "<li><a href='api/modules.php'>Test Modules API</a></li>";
echo "</ul>";
?>
