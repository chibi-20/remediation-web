<?php
require_once '../config.php';

try {
    $db = Database::getInstance();
    
    // Create teachers table
    $createTeachersTable = "
    CREATE TABLE IF NOT EXISTS teachers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100),
        subject VARCHAR(100),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    
    $db->exec($createTeachersTable);
    
    echo "<h2>✅ Teachers Table Created Successfully!</h2>";
    
    // Migrate existing admins to teachers table (except the master admin)
    $stmt = $db->prepare("SELECT * FROM admins WHERE username != ?");
    $stmt->execute(['307901']);
    $existingAdmins = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($existingAdmins)) {
        echo "<h3>🔄 Migrating existing admins to teachers table...</h3>";
        
        foreach ($existingAdmins as $admin) {
            // Check if teacher already exists
            $checkStmt = $db->prepare("SELECT id FROM teachers WHERE username = ?");
            $checkStmt->execute([$admin['username']]);
            
            if (!$checkStmt->fetch()) {
                $insertStmt = $db->prepare("INSERT INTO teachers (username, password, name, created_at) VALUES (?, ?, ?, ?)");
                $insertStmt->execute([
                    $admin['username'],
                    $admin['password'],
                    $admin['username'], // Use username as name for now
                    $admin['created_at']
                ]);
                echo "<p>✅ Migrated: {$admin['username']}</p>";
            } else {
                echo "<p>⚠️ Already exists: {$admin['username']}</p>";
            }
        }
        
        // Remove migrated admins (keep only master admin)
        $deleteStmt = $db->prepare("DELETE FROM admins WHERE username != ?");
        $deleteStmt->execute(['307901']);
        echo "<p>🗑️ Cleaned up admins table (kept master admin only)</p>";
    }
    
    echo "<h3>📊 Current Status:</h3>";
    
    // Show admins count
    $adminCount = $db->query("SELECT COUNT(*) FROM admins")->fetchColumn();
    echo "<p><strong>Admins:</strong> $adminCount (should be 1 - master admin only)</p>";
    
    // Show teachers count
    $teacherCount = $db->query("SELECT COUNT(*) FROM teachers")->fetchColumn();
    echo "<p><strong>Teachers:</strong> $teacherCount</p>";
    
    echo "<hr>";
    echo "<h3>🔗 Next Steps:</h3>";
    echo "<p>✅ Database separation complete!</p>";
    echo "<p>📝 Now updating APIs to use the new structure...</p>";
    
} catch (Exception $e) {
    echo "<h2>❌ Error: " . $e->getMessage() . "</h2>";
}
?>
