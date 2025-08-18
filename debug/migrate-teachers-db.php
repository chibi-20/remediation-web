<?php
// migrate-teachers-db.php - Update database structure for enhanced teacher system
require_once 'config.php';

header('Content-Type: application/json');

try {
    // Update modules table to include new fields
    $stmt = $pdo->query("SHOW COLUMNS FROM modules LIKE 'title'");
    if ($stmt->rowCount() == 0) {
        echo "Adding new columns to modules table...\n";
        $pdo->exec("ALTER TABLE modules 
                   ADD COLUMN title VARCHAR(255) AFTER id,
                   ADD COLUMN description TEXT AFTER title,
                   ADD COLUMN section VARCHAR(100) AFTER description,
                   ADD COLUMN passing_score INT DEFAULT 75 AFTER section");
        echo "✅ Modules table updated\n";
    } else {
        echo "✅ Modules table already has new columns\n";
    }
    
    // Create assessments table for storing student assessment results
    $pdo->exec("CREATE TABLE IF NOT EXISTS assessments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        student_id INT,
        module_id INT,
        score DECIMAL(5,2),
        passed BOOLEAN,
        answers JSON,
        taken_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
        FOREIGN KEY (module_id) REFERENCES modules(id) ON DELETE CASCADE,
        UNIQUE KEY unique_student_module (student_id, module_id)
    )");
    echo "✅ Assessments table created/verified\n";
    
    // Add password field to students table if missing (for assessments)
    $stmt = $pdo->query("SHOW COLUMNS FROM students LIKE 'password'");
    if ($stmt->rowCount() == 0) {
        echo "Adding password field to students table...\n";
        $pdo->exec("ALTER TABLE students ADD COLUMN password VARCHAR(255) AFTER lrn");
        echo "✅ Students table updated with password field\n";
    } else {
        echo "✅ Students table already has password field\n";
    }
    
    // Verify admins table structure
    $stmt = $pdo->query("DESCRIBE admins");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $requiredColumns = ['id', 'name', 'grade', 'subject', 'sections', 'username', 'password', 'created_at'];
    $missingColumns = array_diff($requiredColumns, $columns);
    
    if (empty($missingColumns)) {
        echo "✅ Admins table has all required columns\n";
    } else {
        echo "❌ Missing columns in admins table: " . implode(', ', $missingColumns) . "\n";
    }
    
    echo "\n=== Database Structure Summary ===\n";
    echo "📋 Admins Table: id, name, grade, subject, sections, username, password, created_at\n";
    echo "👨‍🎓 Students Table: id, firstName, lastName, section, lrn, password, progress, admin_id, created_at\n";
    echo "📚 Modules Table: id, title, description, section, passing_score, quarter, filename, questions, admin_id, created_at\n";
    echo "📝 Assessments Table: id, student_id, module_id, score, passed, answers, taken_at\n";
    
    echo "\n✅ Database migration completed successfully!\n";
    
} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
}
?>
