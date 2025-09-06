<?php
// remove-admin-id-column.php - Remove admin_id column from modules table
require_once 'config.php';

try {
    $db = new Database();
    $pdo = $db->getConnection();
    
    echo "<h2>Remove admin_id Column from Modules Table</h2>";
    echo "<pre>";
    
    // Step 1: Check current modules table structure
    echo "1. Current modules table structure:\n";
    $stmt = $pdo->query("DESCRIBE modules");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $hasAdminId = false;
    $hasTeacherId = false;
    
    foreach ($columns as $column) {
        echo "   - {$column['Field']}: {$column['Type']}\n";
        if ($column['Field'] === 'admin_id') $hasAdminId = true;
        if ($column['Field'] === 'teacher_id') $hasTeacherId = true;
    }
    
    // Step 2: Check if teacher_id exists
    if (!$hasTeacherId) {
        echo "\n2. Adding teacher_id column first...\n";
        $pdo->exec("ALTER TABLE modules ADD COLUMN teacher_id INT AFTER admin_id");
        echo "   ✓ teacher_id column added\n";
    } else {
        echo "\n2. teacher_id column already exists ✓\n";
    }
    
    // Step 3: Show modules data before migration
    echo "\n3. Current modules data:\n";
    $stmt = $pdo->query("SELECT id, title, admin_id, teacher_id FROM modules");
    $modules = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($modules as $module) {
        $adminId = $module['admin_id'] ?? 'NULL';
        $teacherId = $module['teacher_id'] ?? 'NULL';
        echo "   - Module {$module['id']}: {$module['title']} | admin_id: {$adminId} | teacher_id: {$teacherId}\n";
    }
    
    // Step 4: Migrate data from admin_id to teacher_id if needed
    echo "\n4. Migrating admin_id to teacher_id...\n";
    
    // Check if there are modules with admin_id but no teacher_id
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM modules WHERE admin_id IS NOT NULL AND (teacher_id IS NULL OR teacher_id = 0)");
    $needsMigration = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    if ($needsMigration > 0) {
        echo "   Found {$needsMigration} modules that need migration...\n";
        
        // For simplicity, let's map admin_id to teacher_id directly
        // You might want to customize this mapping based on your specific needs
        $stmt = $pdo->prepare("UPDATE modules SET teacher_id = admin_id WHERE teacher_id IS NULL OR teacher_id = 0");
        $stmt->execute();
        $updated = $stmt->rowCount();
        
        echo "   ✓ Migrated {$updated} modules from admin_id to teacher_id\n";
    } else {
        echo "   ✓ No migration needed - all modules already have teacher_id\n";
    }
    
    // Step 5: Show modules data after migration
    echo "\n5. Modules data after migration:\n";
    $stmt = $pdo->query("SELECT id, title, admin_id, teacher_id FROM modules");
    $modules = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($modules as $module) {
        $adminId = $module['admin_id'] ?? 'NULL';
        $teacherId = $module['teacher_id'] ?? 'NULL';
        echo "   - Module {$module['id']}: {$module['title']} | admin_id: {$adminId} | teacher_id: {$teacherId}\n";
    }
    
    // Step 6: Remove admin_id column
    if ($hasAdminId) {
        echo "\n6. Removing admin_id column...\n";
        
        if (isset($_GET['confirm']) && $_GET['confirm'] === 'yes') {
            // First drop any foreign key constraints on admin_id
            try {
                $pdo->exec("ALTER TABLE modules DROP FOREIGN KEY fk_modules_admin");
                echo "   ✓ Dropped foreign key constraint\n";
            } catch (Exception $e) {
                echo "   ℹ No foreign key constraint to drop (or already dropped)\n";
            }
            
            // Remove the admin_id column
            $pdo->exec("ALTER TABLE modules DROP COLUMN admin_id");
            echo "   ✓ admin_id column removed successfully!\n";
            
            // Show final structure
            echo "\n7. Final modules table structure:\n";
            $stmt = $pdo->query("DESCRIBE modules");
            $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($columns as $column) {
                echo "   - {$column['Field']}: {$column['Type']}\n";
            }
            
            echo "\n✅ admin_id column removed successfully!\n";
            echo "✅ Modules table now uses teacher_id only.\n";
            
        } else {
            echo "\n6. Ready to remove admin_id column...\n";
            echo "   ⚠️  WARNING: This will permanently delete the admin_id column!\n";
            echo "   ⚠️  Make sure you have a database backup before proceeding.\n";
            echo "\n   To proceed, add ?confirm=yes to the URL:\n";
            echo "   Example: http://localhost/tms/remediation-web/remove-admin-id-column.php?confirm=yes\n";
        }
    } else {
        echo "\n6. admin_id column doesn't exist - already removed! ✓\n";
    }
    
    echo "</pre>";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
