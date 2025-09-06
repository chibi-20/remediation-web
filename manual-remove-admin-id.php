<?php
// manual-remove-admin-id.php - Manually remove admin_id column with proper foreign key handling
require_once 'config.php';

try {
    $db = new Database();
    $pdo = $db->getConnection();
    
    echo "<h2>Manual Remove admin_id Column</h2>";
    echo "<pre>";
    
    // Step 1: Find all foreign key constraints on modules table
    echo "1. Finding foreign key constraints on modules table...\n";
    
    $stmt = $pdo->query("
        SELECT 
            CONSTRAINT_NAME,
            COLUMN_NAME,
            REFERENCED_TABLE_NAME,
            REFERENCED_COLUMN_NAME
        FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
        WHERE TABLE_NAME = 'modules' 
        AND TABLE_SCHEMA = DATABASE()
        AND REFERENCED_TABLE_NAME IS NOT NULL
    ");
    
    $constraints = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($constraints)) {
        echo "   No foreign key constraints found.\n";
    } else {
        echo "   Found foreign key constraints:\n";
        foreach ($constraints as $constraint) {
            echo "   - {$constraint['CONSTRAINT_NAME']}: {$constraint['COLUMN_NAME']} -> {$constraint['REFERENCED_TABLE_NAME']}.{$constraint['REFERENCED_COLUMN_NAME']}\n";
        }
    }
    
    // Step 2: Drop foreign key constraints one by one
    echo "\n2. Dropping foreign key constraints...\n";
    
    $adminIdConstraints = [];
    foreach ($constraints as $constraint) {
        if ($constraint['COLUMN_NAME'] === 'admin_id') {
            $adminIdConstraints[] = $constraint['CONSTRAINT_NAME'];
        }
    }
    
    if (empty($adminIdConstraints)) {
        echo "   No foreign key constraints on admin_id column.\n";
    } else {
        foreach ($adminIdConstraints as $constraintName) {
            try {
                $pdo->exec("ALTER TABLE modules DROP FOREIGN KEY `{$constraintName}`");
                echo "   ✓ Dropped foreign key constraint: {$constraintName}\n";
            } catch (Exception $e) {
                echo "   ✗ Failed to drop {$constraintName}: " . $e->getMessage() . "\n";
            }
        }
    }
    
    // Step 3: Drop any indexes on admin_id
    echo "\n3. Dropping indexes on admin_id column...\n";
    
    $stmt = $pdo->query("
        SELECT INDEX_NAME 
        FROM INFORMATION_SCHEMA.STATISTICS 
        WHERE TABLE_NAME = 'modules' 
        AND TABLE_SCHEMA = DATABASE()
        AND COLUMN_NAME = 'admin_id'
        AND INDEX_NAME != 'PRIMARY'
    ");
    
    $indexes = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($indexes)) {
        echo "   No indexes found on admin_id column.\n";
    } else {
        foreach ($indexes as $indexName) {
            try {
                $pdo->exec("ALTER TABLE modules DROP INDEX `{$indexName}`");
                echo "   ✓ Dropped index: {$indexName}\n";
            } catch (Exception $e) {
                echo "   ✗ Failed to drop index {$indexName}: " . $e->getMessage() . "\n";
            }
        }
    }
    
    // Step 4: Show current table structure before removal
    echo "\n4. Current table structure:\n";
    $stmt = $pdo->query("DESCRIBE modules");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($columns as $column) {
        echo "   - {$column['Field']}: {$column['Type']}\n";
    }
    
    // Step 5: Try to remove the admin_id column
    echo "\n5. Attempting to remove admin_id column...\n";
    
    if (isset($_GET['execute']) && $_GET['execute'] === 'yes') {
        try {
            $pdo->exec("ALTER TABLE modules DROP COLUMN admin_id");
            echo "   ✅ Successfully removed admin_id column!\n";
            
            // Show final structure
            echo "\n6. Final table structure:\n";
            $stmt = $pdo->query("DESCRIBE modules");
            $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($columns as $column) {
                echo "   - {$column['Field']}: {$column['Type']}\n";
            }
            
            echo "\n🎉 admin_id column has been completely removed!\n";
            echo "🎉 Modules table now uses teacher_id only.\n";
            
        } catch (Exception $e) {
            echo "   ✗ Error removing admin_id column: " . $e->getMessage() . "\n";
            
            // Provide manual SQL commands
            echo "\n   Manual SQL commands to try:\n";
            echo "   1. SHOW CREATE TABLE modules;\n";
            echo "   2. ALTER TABLE modules DROP COLUMN admin_id;\n";
        }
    } else {
        echo "   Ready to remove admin_id column.\n";
        echo "   \n   To execute the removal, add ?execute=yes to the URL:\n";
        echo "   Example: http://localhost/tms/remediation-web/manual-remove-admin-id.php?execute=yes\n";
    }
    
    // Step 6: Alternative manual approach
    if (!isset($_GET['execute'])) {
        echo "\n6. Alternative: Manual SQL Commands\n";
        echo "   If the script fails, you can run these SQL commands manually in phpMyAdmin:\n\n";
        
        // Show the commands to run manually
        foreach ($adminIdConstraints as $constraintName) {
            echo "   ALTER TABLE modules DROP FOREIGN KEY `{$constraintName}`;\n";
        }
        
        foreach ($indexes as $indexName) {
            echo "   ALTER TABLE modules DROP INDEX `{$indexName}`;\n";
        }
        
        echo "   ALTER TABLE modules DROP COLUMN admin_id;\n";
    }
    
    echo "</pre>";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
