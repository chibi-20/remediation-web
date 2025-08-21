<?php
require_once '../config.php';

try {
    $db = Database::getInstance();
    
    // Add new columns to teachers table if they don't exist
    $columns = [
        'grade' => "VARCHAR(10) DEFAULT NULL",
        'sections' => "TEXT DEFAULT NULL"
    ];
    
    foreach ($columns as $columnName => $columnDefinition) {
        try {
            // Check if column exists
            $stmt = $db->prepare("SHOW COLUMNS FROM teachers LIKE ?");
            $stmt->execute([$columnName]);
            
            if ($stmt->rowCount() == 0) {
                // Column doesn't exist, add it
                $alterSQL = "ALTER TABLE teachers ADD COLUMN $columnName $columnDefinition";
                $db->exec($alterSQL);
                echo "<p>✅ Added column '$columnName' to teachers table</p>";
            } else {
                echo "<p>ℹ️ Column '$columnName' already exists in teachers table</p>";
            }
        } catch (Exception $e) {
            echo "<p>❌ Error adding column '$columnName': " . $e->getMessage() . "</p>";
        }
    }
    
    // Update students table to include more fields if they don't exist
    $studentColumns = [
        'grade' => "VARCHAR(10) DEFAULT NULL",
        'lrn' => "VARCHAR(20) DEFAULT NULL",
        'teacher_id' => "INT DEFAULT NULL"
    ];
    
    foreach ($studentColumns as $columnName => $columnDefinition) {
        try {
            // Check if column exists
            $stmt = $db->prepare("SHOW COLUMNS FROM students LIKE ?");
            $stmt->execute([$columnName]);
            
            if ($stmt->rowCount() == 0) {
                // Column doesn't exist, add it
                $alterSQL = "ALTER TABLE students ADD COLUMN $columnName $columnDefinition";
                $db->exec($alterSQL);
                echo "<p>✅ Added column '$columnName' to students table</p>";
            } else {
                echo "<p>ℹ️ Column '$columnName' already exists in students table</p>";
            }
        } catch (Exception $e) {
            echo "<p>❌ Error adding column '$columnName': " . $e->getMessage() . "</p>";
        }
    }
    
    echo "<h3>✅ Database schema updated successfully!</h3>";
    
} catch (Exception $e) {
    echo "<h2>❌ Error: " . $e->getMessage() . "</h2>";
}
?>
