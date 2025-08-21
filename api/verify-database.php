<?php
require_once '../config.php';

try {
    $db = Database::getInstance();
    
    echo "<h2>📊 Final Database Status Check</h2>";
    
    // Show all tables
    $stmt = $db->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<h3>📋 Available Tables:</h3><ul>";
    foreach ($tables as $table) {
        echo "<li>$table</li>";
    }
    echo "</ul>";
    
    // Detailed table information
    echo "<hr>";
    
    // Admins table
    echo "<h3>👨‍💼 Admins Table (System Administrators):</h3>";
    $adminCount = $db->query("SELECT COUNT(*) FROM admins")->fetchColumn();
    echo "<p><strong>Count:</strong> $adminCount</p>";
    
    if ($adminCount > 0) {
        $stmt = $db->query("SELECT id, username, created_at FROM admins ORDER BY created_at");
        $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Username</th><th>Created At</th></tr>";
        foreach ($admins as $admin) {
            echo "<tr><td>{$admin['id']}</td><td>{$admin['username']}</td><td>{$admin['created_at']}</td></tr>";
        }
        echo "</table>";
    }
    
    echo "<hr>";
    
    // Teachers table
    echo "<h3>👩‍🏫 Teachers Table (Educators):</h3>";
    $teacherCount = $db->query("SELECT COUNT(*) FROM teachers")->fetchColumn();
    echo "<p><strong>Count:</strong> $teacherCount</p>";
    
    if ($teacherCount > 0) {
        $stmt = $db->query("SELECT id, username, name, subject, grade, sections, created_at FROM teachers ORDER BY created_at");
        $teachers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Username</th><th>Name</th><th>Subject</th><th>Grade</th><th>Sections</th><th>Created At</th></tr>";
        foreach ($teachers as $teacher) {
            echo "<tr>";
            echo "<td>{$teacher['id']}</td>";
            echo "<td>{$teacher['username']}</td>";
            echo "<td>{$teacher['name']}</td>";
            echo "<td>{$teacher['subject']}</td>";
            echo "<td>{$teacher['grade']}</td>";
            echo "<td>{$teacher['sections']}</td>";
            echo "<td>{$teacher['created_at']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    echo "<hr>";
    
    // Students table
    echo "<h3>👨‍🎓 Students Table:</h3>";
    $studentCount = $db->query("SELECT COUNT(*) FROM students")->fetchColumn();
    echo "<p><strong>Count:</strong> $studentCount</p>";
    
    if ($studentCount > 0) {
        $stmt = $db->query("SELECT id, username, name, grade, lrn, teacher_id, created_at FROM students ORDER BY created_at LIMIT 10");
        $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Username</th><th>Name</th><th>Grade</th><th>LRN</th><th>Teacher ID</th><th>Created At</th></tr>";
        foreach ($students as $student) {
            echo "<tr>";
            echo "<td>{$student['id']}</td>";
            echo "<td>{$student['username']}</td>";
            echo "<td>{$student['name']}</td>";
            echo "<td>{$student['grade']}</td>";
            echo "<td>{$student['lrn']}</td>";
            echo "<td>{$student['teacher_id']}</td>";
            echo "<td>{$student['created_at']}</td>";
            echo "</tr>";
        }
        echo "</table>";
        if ($studentCount > 10) {
            echo "<p><em>Showing first 10 of $studentCount students</em></p>";
        }
    }
    
    echo "<hr>";
    echo "<h3>✅ Database Structure Summary:</h3>";
    echo "<ul>";
    echo "<li><strong>admins</strong>: $adminCount records (system administrators only)</li>";
    echo "<li><strong>teachers</strong>: $teacherCount records (educators/subject teachers)</li>";
    echo "<li><strong>students</strong>: $studentCount records (learners)</li>";
    echo "</ul>";
    
    if ($adminCount == 1 && $teacherCount > 0) {
        echo "<p>🎉 <strong>Perfect!</strong> Database is properly separated:</p>";
        echo "<ul>";
        echo "<li>✅ Single system admin (master account)</li>";
        echo "<li>✅ Teachers in separate table</li>";
        echo "<li>✅ Proper role separation achieved</li>";
        echo "</ul>";
    }
    
} catch (Exception $e) {
    echo "<h2>❌ Error: " . $e->getMessage() . "</h2>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>

<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    table { margin: 10px 0; }
    th { background-color: #f0f0f0; padding: 8px; }
    td { padding: 6px; }
    h2 { color: #333; }
    h3 { color: #666; }
</style>
