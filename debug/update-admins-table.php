<?php
// update-admins-table.php - Ensure admins table has correct structure
require_once 'config.php';

header('Content-Type: text/plain');

try {
    echo "Checking and updating admins table structure...\n\n";
    
    // Check current columns
    $stmt = $pdo->query("SHOW COLUMNS FROM admins");
    $existingColumns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "Current columns: " . implode(', ', $existingColumns) . "\n\n";
    
    $requiredColumns = [
        'name' => 'VARCHAR(255)',
        'grade' => 'VARCHAR(100)', 
        'subject' => 'VARCHAR(255)',
        'sections' => 'TEXT'
    ];
    
    foreach ($requiredColumns as $column => $type) {
        if (!in_array($column, $existingColumns)) {
            echo "Adding missing column: $column\n";
            $pdo->exec("ALTER TABLE admins ADD COLUMN $column $type");
        } else {
            echo "✅ Column $column exists\n";
        }
    }
    
    echo "\nFinal table structure:\n";
    $stmt = $pdo->query("DESCRIBE admins");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($columns as $col) {
        echo "- {$col['Field']} ({$col['Type']})\n";
    }
    
    echo "\n✅ Admins table structure updated successfully!\n";
    
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
