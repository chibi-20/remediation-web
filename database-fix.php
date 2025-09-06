<?php
// database-fix.php - Fix database structure: Move teachers from admins to teachers table
require_once 'config.php';

try {
    $db = new Database();
    $pdo = $db->getConnection();
    
    echo "<h2>Database Teacher Migration</h2>";
    echo "<pre>";
    
    // Step 1: Check table structures first
    echo "1. Checking table structures:\n";
    
    // Check admins table structure
    $stmt = $pdo->query("DESCRIBE admins");
    $adminColumns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "   Admins table columns: " . implode(', ', $adminColumns) . "\n";
    
    // Check teachers table structure  
    $stmt = $pdo->query("DESCRIBE teachers");
    $teacherColumns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "   Teachers table columns: " . implode(', ', $teacherColumns) . "\n";
    
    // Step 2: Show current state
    echo "\n2. Current database state:\n";
    
    // Build dynamic query based on available columns
    $adminSelectFields = ['id', 'username'];
    if (in_array('email', $adminColumns)) $adminSelectFields[] = 'email';
    if (in_array('created_at', $adminColumns)) $adminSelectFields[] = 'created_at';
    
    $adminQuery = "SELECT " . implode(', ', $adminSelectFields) . " FROM admins";
    $stmt = $pdo->query($adminQuery);
    $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "   Admins table:\n";
    foreach ($admins as $admin) {
        $isRealAdmin = ($admin['id'] == 1 || strpos($admin['username'], 'admin') !== false || $admin['username'] == '307901');
        $status = $isRealAdmin ? "[KEEP - Real Admin]" : "[MOVE - Teacher]";
        $email = isset($admin['email']) ? $admin['email'] : 'N/A';
        echo "   - ID: {$admin['id']}, Username: {$admin['username']}, Email: {$email} {$status}\n";
    }
    
    $stmt = $pdo->query("SELECT id, username, name, subject FROM teachers");
    $teachers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "\n   Teachers table:\n";
    foreach ($teachers as $teacher) {
        echo "   - ID: {$teacher['id']}, Username: {$teacher['username']}, Name: {$teacher['name']}, Subject: {$teacher['subject']}\n";
    }
    
    // Step 2: Add teacher_id column to modules if it doesn't exist
    echo "\n2. Updating modules table structure...\n";
    try {
        $pdo->exec("ALTER TABLE modules ADD COLUMN teacher_id INT AFTER admin_id");
        echo "   ✓ teacher_id column added to modules table\n";
    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
            echo "   ✓ teacher_id column already exists\n";
        } else {
            echo "   ✗ Error adding teacher_id: " . $e->getMessage() . "\n";
        }
    }
    
    // Step 3: Migrate teachers from admins to teachers table
    echo "\n3. Migrating teachers from admins to teachers table...\n";
    
    // Get teachers from admins table (exclude Master Administrator)
    $excludeAdminQuery = "SELECT * FROM admins WHERE id != 1 AND username != '307901'";
    $stmt = $pdo->prepare($excludeAdminQuery);
    $stmt->execute();
    $teachersInAdminTable = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $migratedTeachers = [];
    
    foreach ($teachersInAdminTable as $teacherAdmin) {
        // Check if teacher already exists in teachers table
        $stmt = $pdo->prepare("SELECT id FROM teachers WHERE username = ?");
        $stmt->execute([$teacherAdmin['username']]);
        $existingTeacher = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($existingTeacher) {
            echo "   ↻ Teacher {$teacherAdmin['username']} already exists in teachers table (ID: {$existingTeacher['id']})\n";
            $migratedTeachers[$teacherAdmin['id']] = $existingTeacher['id'];
        } else {
            // Migrate teacher to teachers table
            $stmt = $pdo->prepare("
                INSERT INTO teachers (username, password, name, email, grade, subject, advisory_section, sections, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            // Extract data from admin record - handle missing columns
            $name = $teacherAdmin['username']; // Use username as name if no name field
            $email = isset($teacherAdmin['email']) ? $teacherAdmin['email'] : null;
            $password = $teacherAdmin['password'];
            $grade = '10'; // Default grade, you can modify this
            $subject = 'ENGLISH'; // Default subject, you can modify this  
            $advisory_section = '';
            $sections = '';
            $created_at = isset($teacherAdmin['created_at']) ? $teacherAdmin['created_at'] : date('Y-m-d H:i:s');
            
            $stmt->execute([
                $teacherAdmin['username'],
                $password,
                $name,
                $email,
                $grade,
                $subject,
                $advisory_section,
                $sections,
                $created_at
            ]);
            
            $newTeacherId = $pdo->lastInsertId();
            $migratedTeachers[$teacherAdmin['id']] = $newTeacherId;
            
            echo "   ✓ Migrated {$teacherAdmin['username']} from admin ID {$teacherAdmin['id']} to teacher ID {$newTeacherId}\n";
        }
    }
    
    // Step 4: Update modules to use teacher_id instead of admin_id
    echo "\n4. Updating modules to reference teachers...\n";
    
    foreach ($migratedTeachers as $oldAdminId => $newTeacherId) {
        $stmt = $pdo->prepare("UPDATE modules SET teacher_id = ? WHERE admin_id = ?");
        $stmt->execute([$newTeacherId, $oldAdminId]);
        $affectedRows = $stmt->rowCount();
        
        if ($affectedRows > 0) {
            echo "   ✓ Updated {$affectedRows} modules from admin_id {$oldAdminId} to teacher_id {$newTeacherId}\n";
        }
    }
    
    // Step 5: Show updated state
    echo "\n5. Updated database state:\n";
    
    $adminSelectFields = ['id', 'username'];
    if (in_array('email', $adminColumns)) $adminSelectFields[] = 'email';
    
    $adminQuery = "SELECT " . implode(', ', $adminSelectFields) . " FROM admins";
    $stmt = $pdo->query($adminQuery);
    $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "   Admins table (should only have real admins):\n";
    foreach ($admins as $admin) {
        $email = isset($admin['email']) ? $admin['email'] : 'N/A';
        echo "   - ID: {$admin['id']}, Username: {$admin['username']}, Email: {$email}\n";
    }
    
    $stmt = $pdo->query("SELECT id, username, name, subject FROM teachers");
    $teachers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "\n   Teachers table:\n";
    foreach ($teachers as $teacher) {
        echo "   - ID: {$teacher['id']}, Username: {$teacher['username']}, Name: {$teacher['name']}, Subject: {$teacher['subject']}\n";
    }
    
    $stmt = $pdo->query("SELECT id, title, admin_id, teacher_id FROM modules");
    $modules = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "\n   Modules:\n";
    foreach ($modules as $module) {
        echo "   - ID: {$module['id']}, Title: {$module['title']}, Admin ID: {$module['admin_id']}, Teacher ID: {$module['teacher_id']}\n";
    }
    
    // Step 6: Ask for confirmation to delete teachers from admins table
    echo "\n6. Ready to clean up admins table...\n";
    echo "   The following teacher accounts will be DELETED from admins table:\n";
    
    foreach ($teachersInAdminTable as $teacherAdmin) {
        if (isset($migratedTeachers[$teacherAdmin['id']])) {
            echo "   - Will DELETE admin ID {$teacherAdmin['id']} ({$teacherAdmin['username']}) - migrated to teacher ID {$migratedTeachers[$teacherAdmin['id']]}\n";
        }
    }
    
    echo "\n   To complete the cleanup, run this script with ?cleanup=true parameter\n";
    echo "   Example: http://localhost/tms/remediation-web/database-fix.php?cleanup=true\n";
    
    // Step 7: Actually delete if cleanup parameter is provided
    if (isset($_GET['cleanup']) && $_GET['cleanup'] === 'true') {
        echo "\n7. Executing cleanup...\n";
        
        foreach ($teachersInAdminTable as $teacherAdmin) {
            if (isset($migratedTeachers[$teacherAdmin['id']])) {
                $stmt = $pdo->prepare("DELETE FROM admins WHERE id = ? AND id != 1");
                $stmt->execute([$teacherAdmin['id']]);
                echo "   ✓ Deleted admin ID {$teacherAdmin['id']} ({$teacherAdmin['username']})\n";
            }
        }
        
        echo "\n✅ Cleanup completed! Teachers are now properly separated from admins.\n";
    }
    
    echo "\n✅ Migration completed successfully!\n";
    echo "Now teachers are in the teachers table and modules reference teachers properly.\n";
    
    echo "</pre>";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
