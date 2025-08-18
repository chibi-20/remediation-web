<?php
// check-database.php - Check current database structure
require_once 'config.php';

header('Content-Type: text/plain');

try {
    echo "=== ADMINS TABLE STRUCTURE ===\n";
    $stmt = $pdo->query("DESCRIBE admins");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($columns as $col) {
        echo "- {$col['Field']} ({$col['Type']}) - {$col['Null']} - {$col['Default']}\n";
    }
    
    echo "\n=== MODULES TABLE STRUCTURE ===\n";
    $stmt = $pdo->query("DESCRIBE modules");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($columns as $col) {
        echo "- {$col['Field']} ({$col['Type']}) - {$col['Null']} - {$col['Default']}\n";
    }
    
    echo "\n=== TESTING SAMPLE INSERT ===\n";
    
    // Test if we can insert with new structure
    $stmt = $pdo->prepare("INSERT INTO admins (name, grade, subject, sections, username, password) VALUES (?, ?, ?, ?, ?, ?)");
    $testResult = $stmt->execute(['Test Teacher', '10', 'Test Subject', '10-A', 'test_user_' . time(), 'test_password']);
    
    if ($testResult) {
        echo "✅ Sample insert successful - database supports new structure\n";
        // Clean up test record
        $pdo->exec("DELETE FROM admins WHERE username LIKE 'test_user_%'");
    } else {
        echo "❌ Sample insert failed\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
}
?>
