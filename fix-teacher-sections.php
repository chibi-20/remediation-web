<?php
// Fix teacher_sections table to use teacher_id instead of admin_id
require_once 'config.php';

try {
    $db = new Database();
    $pdo = $db->getConnection();
    
    echo "Starting teacher_sections table migration...\n";
    
    // Check if teacher_id column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM teacher_sections LIKE 'teacher_id'");
    $hasTeacherId = $stmt->rowCount() > 0;
    
    if (!$hasTeacherId) {
        echo "Adding teacher_id column...\n";
        $pdo->exec("ALTER TABLE teacher_sections ADD COLUMN teacher_id INT(11) AFTER admin_id");
        
        echo "Migrating admin_id to teacher_id...\n";
        $pdo->exec("UPDATE teacher_sections SET teacher_id = admin_id WHERE admin_id IS NOT NULL");
        
        echo "Adding foreign key constraint...\n";
        $pdo->exec("ALTER TABLE teacher_sections ADD FOREIGN KEY (teacher_id) REFERENCES teachers(id)");
        
        // Try to drop admin_id column safely
        try {
            echo "Attempting to drop admin_id column...\n";
            $pdo->exec("ALTER TABLE teacher_sections DROP FOREIGN KEY teacher_sections_ibfk_1");
            $pdo->exec("ALTER TABLE teacher_sections DROP COLUMN admin_id");
            echo "✅ admin_id column dropped successfully!\n";
        } catch (Exception $e) {
            echo "⚠️ Could not drop admin_id column: " . $e->getMessage() . "\n";
            echo "This is okay - teacher_id column is working\n";
        }
        
        echo "✅ teacher_sections table successfully migrated!\n";
    } else {
        echo "teacher_id column already exists in teacher_sections\n";
    }
    
    // Show current data
    echo "\nCurrent teacher_sections data:\n";
    $stmt = $pdo->query("SELECT * FROM teacher_sections");
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    print_r($data);
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
