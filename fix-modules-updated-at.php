<?php
// Add updated_at column to modules table if it doesn't exist
require_once 'config.php';

try {
    $db = new Database();
    $pdo = $db->getConnection();
    
    echo "Checking modules table for updated_at column...\n";
    
    // Check if updated_at column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM modules LIKE 'updated_at'");
    $hasUpdatedAt = $stmt->rowCount() > 0;
    
    if (!$hasUpdatedAt) {
        echo "Adding updated_at column to modules table...\n";
        $pdo->exec("ALTER TABLE modules ADD COLUMN updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP AFTER created_at");
        echo "✅ updated_at column added successfully!\n";
    } else {
        echo "updated_at column already exists in modules table\n";
    }
    
    // Show current modules table structure
    echo "\nCurrent modules table structure:\n";
    $stmt = $pdo->query("DESCRIBE modules");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($columns as $col) {
        echo "- {$col['Field']} ({$col['Type']})\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
