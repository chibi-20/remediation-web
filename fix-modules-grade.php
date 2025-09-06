<?php
// Add grade column to modules table
require_once 'config.php';

try {
    $db = new Database();
    $pdo = $db->getConnection();
    
    echo "Checking modules table structure...\n";
    
    // Check if grade column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM modules LIKE 'grade'");
    $hasGrade = $stmt->rowCount() > 0;
    
    if (!$hasGrade) {
        echo "Adding grade column to modules table...\n";
        $pdo->exec("ALTER TABLE modules ADD COLUMN grade VARCHAR(10) AFTER section");
        
        // Update existing modules with grade based on teacher's grade
        echo "Updating existing modules with teacher's grade...\n";
        $pdo->exec("
            UPDATE modules m 
            JOIN teachers t ON m.teacher_id = t.id 
            SET m.grade = t.grade 
            WHERE m.grade IS NULL
        ");
        
        echo "✅ Grade column added and populated!\n";
    } else {
        echo "Grade column already exists in modules table\n";
    }
    
    // Show sample modules data
    echo "\nSample modules data:\n";
    $stmt = $pdo->query("SELECT id, title, section, grade, teacher_id FROM modules LIMIT 3");
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    print_r($data);
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
