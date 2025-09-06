<?php
require_once 'config.php';

try {
    $db = new Database();
    $pdo = $db->getConnection();
    
    echo "=== CHECKING TEACHERS TABLE ===\n";
    try {
        $stmt = $pdo->query('DESCRIBE teachers');
        echo "Teachers table structure:\n";
        while ($row = $stmt->fetch()) {
            echo $row['Field'] . ' - ' . $row['Type'] . "\n";
        }
        
        echo "\n=== ALL TEACHERS ===\n";
        $stmt = $pdo->query('SELECT * FROM teachers');
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            print_r($row);
        }
    } catch (Exception $e) {
        echo "Teachers table error: " . $e->getMessage() . "\n";
    }
    
    echo "\n=== CURRENT STUDENTS ASSIGNMENT ===\n";
    $stmt = $pdo->query('SELECT id, firstName, lastName, section, teacher_id FROM students');
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "Student: " . $row['firstName'] . " " . $row['lastName'] . " | Section: " . $row['section'] . " | Teacher ID: " . $row['teacher_id'] . "\n";
    }
    
    echo "\n=== MODULES WITH SECTIONS ===\n";
    $stmt = $pdo->query('SELECT id, title, section, teacher_id FROM modules');
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "Module: " . $row['title'] . " | Section: " . $row['section'] . " | Teacher ID: " . $row['teacher_id'] . "\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
