<?php
require_once '../config.php';

try {
    $db = new Database();
    $pdo = $db->getConnection();
    
    echo "Updating students table structure...\n";
    
    // Check current students table structure
    $stmt = $pdo->query("DESCRIBE students");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Current students table columns:\n";
    foreach ($columns as $column) {
        echo "- {$column['Field']} ({$column['Type']})\n";
    }
    
    // Check if we need to add new columns
    $columnNames = array_column($columns, 'Field');
    
    $requiredColumns = [
        'username' => "ALTER TABLE students ADD COLUMN username VARCHAR(50) UNIQUE",
        'password' => "ALTER TABLE students ADD COLUMN password VARCHAR(255)",
        'name' => "ALTER TABLE students ADD COLUMN name VARCHAR(100)",
        'grade' => "ALTER TABLE students ADD COLUMN grade VARCHAR(10)",
        'teacher_id' => "ALTER TABLE students ADD COLUMN teacher_id INT"
    ];
    
    foreach ($requiredColumns as $columnName => $sql) {
        if (!in_array($columnName, $columnNames)) {
            echo "Adding column: $columnName\n";
            $pdo->exec($sql);
            echo "✅ Added $columnName column\n";
        } else {
            echo "✅ Column $columnName already exists\n";
        }
    }
    
    // Update foreign key if needed
    if (in_array('admin_id', $columnNames)) {
        echo "Updating foreign key constraint...\n";
        
        // Drop old foreign key
        try {
            $pdo->exec("ALTER TABLE students DROP FOREIGN KEY students_ibfk_1");
            echo "✅ Dropped old foreign key constraint\n";
        } catch (Exception $e) {
            echo "Note: Old foreign key might not exist: " . $e->getMessage() . "\n";
        }
        
        // Remove admin_id column
        try {
            $pdo->exec("ALTER TABLE students DROP COLUMN admin_id");
            echo "✅ Removed admin_id column\n";
        } catch (Exception $e) {
            echo "Note: admin_id column might not exist: " . $e->getMessage() . "\n";
        }
    }
    
    // Add foreign key for teacher_id if it doesn't exist
    try {
        $pdo->exec("ALTER TABLE students ADD CONSTRAINT fk_students_teacher FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE SET NULL");
        echo "✅ Added teacher foreign key constraint\n";
    } catch (Exception $e) {
        echo "Note: Teacher foreign key might already exist: " . $e->getMessage() . "\n";
    }
    
    echo "\n✅ Students table structure updated successfully!\n";
    
    // Show final structure
    $stmt = $pdo->query("DESCRIBE students");
    $finalColumns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "\nFinal students table structure:\n";
    foreach ($finalColumns as $column) {
        echo "- {$column['Field']} ({$column['Type']}) {$column['Key']}\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
?>
