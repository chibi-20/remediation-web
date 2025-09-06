<?php
require_once 'config.php';

try {
    $db = new Database();
    $pdo = $db->getConnection();
    
    echo "=== ALL TABLES ===\n";
    $stmt = $pdo->query('SHOW TABLES');
    while ($row = $stmt->fetch()) {
        echo $row[0] . "\n";
    }
    
    echo "\n=== CHECKING FOR ASSESSMENT/PROGRESS TABLES ===\n";
    
    // Check for student_progress table
    try {
        $stmt = $pdo->query('DESCRIBE student_progress');
        echo "\n--- student_progress table structure ---\n";
        while ($row = $stmt->fetch()) {
            echo $row['Field'] . " - " . $row['Type'] . "\n";
        }
        
        // Sample data
        $stmt = $pdo->query('SELECT * FROM student_progress LIMIT 5');
        echo "\n--- Sample student_progress data ---\n";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            print_r($row);
        }
    } catch (Exception $e) {
        echo "student_progress table not found\n";
    }
    
    // Check for assessments table
    try {
        $stmt = $pdo->query('DESCRIBE assessments');
        echo "\n--- assessments table structure ---\n";
        while ($row = $stmt->fetch()) {
            echo $row['Field'] . " - " . $row['Type'] . "\n";
        }
        
        // Sample data
        $stmt = $pdo->query('SELECT * FROM assessments LIMIT 5');
        echo "\n--- Sample assessments data ---\n";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            print_r($row);
        }
    } catch (Exception $e) {
        echo "assessments table not found\n";
    }
    
    // Check for module_progress table
    try {
        $stmt = $pdo->query('DESCRIBE module_progress');
        echo "\n--- module_progress table structure ---\n";
        while ($row = $stmt->fetch()) {
            echo $row['Field'] . " - " . $row['Type'] . "\n";
        }
        
        // Sample data
        $stmt = $pdo->query('SELECT * FROM module_progress LIMIT 5');
        echo "\n--- Sample module_progress data ---\n";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            print_r($row);
        }
    } catch (Exception $e) {
        echo "module_progress table not found\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
