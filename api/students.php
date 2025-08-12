<?php
// api/students.php - Get all students endpoint
require_once '../config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(['success' => false, 'error' => 'Method not allowed'], 405);
}

try {
    $stmt = $pdo->prepare("SELECT * FROM students");
    $stmt->execute();
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Parse progress JSON for each student
    foreach ($students as &$student) {
        $student['progress'] = $student['progress'] ? json_decode($student['progress'], true) : [];
    }
    
    jsonResponse($students);
    
} catch (PDOException $e) {
    jsonResponse(['error' => 'Database error'], 500);
}
?>
