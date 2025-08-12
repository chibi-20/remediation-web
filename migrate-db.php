<?php
// migrate-db.php - Update database schema
require_once 'config.php';

echo "<h1>Database Migration</h1>";

try {
    // Check if the admins table has the new columns
    $stmt = $pdo->query("PRAGMA table_info(admins)");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $hasNameColumn = false;
    $hasGradeColumn = false;
    $hasSubjectColumn = false;
    
    foreach ($columns as $column) {
        if ($column['name'] === 'name') $hasNameColumn = true;
        if ($column['name'] === 'grade') $hasGradeColumn = true;
        if ($column['name'] === 'subject') $hasSubjectColumn = true;
    }
    
    if (!$hasNameColumn || !$hasGradeColumn || !$hasSubjectColumn) {
        echo "<p>Updating admins table schema...</p>";
        
        // Add missing columns
        if (!$hasNameColumn) {
            $pdo->exec("ALTER TABLE admins ADD COLUMN name TEXT");
            echo "<p>✅ Added 'name' column</p>";
        }
        
        if (!$hasGradeColumn) {
            $pdo->exec("ALTER TABLE admins ADD COLUMN grade TEXT");
            echo "<p>✅ Added 'grade' column</p>";
        }
        
        if (!$hasSubjectColumn) {
            $pdo->exec("ALTER TABLE admins ADD COLUMN subject TEXT");
            echo "<p>✅ Added 'subject' column</p>";
        }
        
        echo "<p style='color: green;'>✅ Database schema updated successfully!</p>";
    } else {
        echo "<p style='color: green;'>✅ Database schema is already up to date!</p>";
    }
    
    // Show current admins
    $stmt = $pdo->query("SELECT * FROM admins");
    $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($admins)) {
        echo "<p style='color: orange;'>⚠️ No admin accounts found. You need to register an admin first.</p>";
        echo "<p><a href='public/admin-register.html'>Register Admin Account</a></p>";
    } else {
        echo "<h2>Current Admin Accounts:</h2>";
        echo "<ul>";
        foreach ($admins as $admin) {
            echo "<li>ID: {$admin['id']}, Username: {$admin['username']}, Name: {$admin['name']}</li>";
        }
        echo "</ul>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

echo "<h2>Next Steps</h2>";
echo "<ul>";
echo "<li><a href='public/admin-register.html'>Register Admin Account</a></li>";
echo "<li><a href='public/admin-login.html'>Admin Login</a></li>";
echo "<li><a href='debug.php'>Debug Information</a></li>";
echo "</ul>";
?>
