<?php
require_once 'config.php';

try {
    $db = new Database();
    $pdo = $db->getConnection();
    
    echo "=== ALL STUDENTS ===\n";
    $stmt = $pdo->query('SELECT * FROM students');
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        print_r($row);
    }
    
    echo "\n=== STUDENTS FOR TEACHER_ID = 2 ===\n";
    $stmt = $pdo->query('SELECT * FROM students WHERE teacher_id = 2');
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (empty($students)) {
        echo "No students found for teacher_id = 2\n";
    } else {
        foreach ($students as $student) {
            print_r($student);
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
