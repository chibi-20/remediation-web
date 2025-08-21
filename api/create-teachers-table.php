<?php
require_once '../config.php';

try {
    $db = new Database();
    $pdo = $db->getConnection();
    
    echo "Checking database structure...\n";
    
    // Check current tables
    $stmt = $pdo->query('SHOW TABLES');
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Current tables: " . implode(', ', $tables) . "\n";
    
    // Check if teachers table exists
    $teachersExists = in_array('teachers', $tables);
    echo "Teachers table exists: " . ($teachersExists ? 'YES' : 'NO') . "\n";
    
    if (!$teachersExists) {
        echo "Creating teachers table...\n";
        
        $createSQL = "CREATE TABLE teachers (
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
        
        $pdo->exec($createSQL);
        echo "✅ Teachers table created successfully!\n";
        
        // Now migrate data from admins table (exclude master admin)
        echo "Migrating teachers from admins table...\n";
        
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE username != ?");
        $stmt->execute(['307901']);
        $teachersToMigrate = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($teachersToMigrate as $admin) {
            $insertStmt = $pdo->prepare("INSERT INTO teachers (username, password, name, created_at) VALUES (?, ?, ?, ?)");
            $insertStmt->execute([
                $admin['username'],
                $admin['password'],
                $admin['name'] ?? $admin['username'], // Use name if available, otherwise username
                $admin['created_at']
            ]);
            echo "✅ Migrated teacher: " . $admin['username'] . "\n";
        }
        
        // Remove migrated teachers from admins table (keep master admin)
        $deleteStmt = $pdo->prepare("DELETE FROM admins WHERE username != ?");
        $deleteStmt->execute(['307901']);
        echo "🗑️ Cleaned up admins table\n";
        
        // Verify results
        $teacherCount = $pdo->query("SELECT COUNT(*) FROM teachers")->fetchColumn();
        $adminCount = $pdo->query("SELECT COUNT(*) FROM admins")->fetchColumn();
        
        echo "\n✅ Migration complete!\n";
        echo "Teachers: $teacherCount\n";
        echo "Admins: $adminCount\n";
        
    } else {
        echo "Teachers table already exists.\n";
        
        $teacherCount = $pdo->query("SELECT COUNT(*) FROM teachers")->fetchColumn();
        $adminCount = $pdo->query("SELECT COUNT(*) FROM admins")->fetchColumn();
        
        echo "Teachers: $teacherCount\n";
        echo "Admins: $adminCount\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
?>
