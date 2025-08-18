<?php
// api/students.php - Get students for authenticated teacher
require_once '../config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(['success' => false, 'error' => 'Method not allowed'], 405);
}

// Check authentication
startSession();
if (!isset($_SESSION['admin_id'])) {
    jsonResponse(['success' => false, 'error' => 'Authentication required'], 401);
}

$adminId = $_SESSION['admin_id'];

try {
    // Only get students that belong to this specific teacher (admin)
    $stmt = $pdo->prepare("SELECT * FROM students WHERE admin_id = ?");
    $stmt->execute([$adminId]);
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
