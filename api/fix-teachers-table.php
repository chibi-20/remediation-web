<?php
require_once '../config.php';

try {
    $db = Database::getInstance();
    
    echo "<h2>🔍 Current Database Status</h2>";
    
    // Check if teachers table exists
    $stmt = $db->prepare("SHOW TABLES LIKE 'teachers'");
    $stmt->execute();
    $teachersTableExists = $stmt->rowCount() > 0;
    
    echo "<p><strong>Teachers table exists:</strong> " . ($teachersTableExists ? "✅ Yes" : "❌ No") . "</p>";
    
    if ($teachersTableExists) {
        // Count teachers
        $teacherCount = $db->query("SELECT COUNT(*) FROM teachers")->fetchColumn();
        echo "<p><strong>Teachers count:</strong> $teacherCount</p>";
        
        // Show sample teachers
        $stmt = $db->query("SELECT id, username, name, subject FROM teachers LIMIT 5");
        $teachers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (!empty($teachers)) {
            echo "<h3>Sample Teachers:</h3><ul>";
            foreach ($teachers as $teacher) {
                echo "<li>ID: {$teacher['id']}, Username: {$teacher['username']}, Name: {$teacher['name']}, Subject: {$teacher['subject']}</li>";
            }
            echo "</ul>";
        }
    }
    
    // Check admins table
    $adminCount = $db->query("SELECT COUNT(*) FROM admins")->fetchColumn();
    echo "<p><strong>Admins count:</strong> $adminCount</p>";
    
    // Show admins
    $stmt = $db->query("SELECT id, username, created_at FROM admins");
    $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>Current Admins:</h3><ul>";
    foreach ($admins as $admin) {
        echo "<li>ID: {$admin['id']}, Username: {$admin['username']}, Created: {$admin['created_at']}</li>";
    }
    echo "</ul>";
    
    if (!$teachersTableExists) {
        echo "<hr><h2>🛠️ Creating Teachers Table</h2>";
        
        // Create teachers table
        $createTeachersTable = "
        CREATE TABLE teachers (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(100),
            subject VARCHAR(100),
            grade VARCHAR(10),
            sections TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        
        $db->exec($createTeachersTable);
        echo "<p>✅ Teachers table created successfully!</p>";
        
        // Migrate existing admins to teachers (except master admin)
        $stmt = $db->prepare("SELECT * FROM admins WHERE username != ?");
        $stmt->execute(['307901']);
        $existingAdmins = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (!empty($existingAdmins)) {
            echo "<h3>🔄 Migrating admins to teachers table...</h3>";
            
            foreach ($existingAdmins as $admin) {
                $insertStmt = $db->prepare("INSERT INTO teachers (username, password, name, created_at) VALUES (?, ?, ?, ?)");
                $insertStmt->execute([
                    $admin['username'],
                    $admin['password'],
                    $admin['username'], // Use username as name for now
                    $admin['created_at']
                ]);
                echo "<p>✅ Migrated: {$admin['username']}</p>";
            }
            
            // Remove migrated admins (keep only master admin)
            $deleteStmt = $db->prepare("DELETE FROM admins WHERE username != ?");
            $deleteStmt->execute(['307901']);
            echo "<p>🗑️ Cleaned up admins table (kept master admin only)</p>";
        }
        
        echo "<h3>✅ Migration Complete!</h3>";
        
        // Show final counts
        $finalTeacherCount = $db->query("SELECT COUNT(*) FROM teachers")->fetchColumn();
        $finalAdminCount = $db->query("SELECT COUNT(*) FROM admins")->fetchColumn();
        
        echo "<p><strong>Final Teachers count:</strong> $finalTeacherCount</p>";
        echo "<p><strong>Final Admins count:</strong> $finalAdminCount (should be 1 - master admin only)</p>";
    }
    
} catch (Exception $e) {
    echo "<h2>❌ Error: " . $e->getMessage() . "</h2>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>
