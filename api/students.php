<?php
// api/students.php - Get students for authenticated teacher
require_once '../config.php';
require_once '../security-middleware.php';

// Apply API security checks
SecurityMiddleware::checkAPISecurity();

header('Content-Type: application/json');

// Check teacher authentication
session_start();
if (!isset($_SESSION['teacher_logged_in']) || !$_SESSION['teacher_logged_in'] || !isset($_SESSION['teacher_id'])) {
    jsonResponse(false, 'Teacher authentication required');
}

$teacherId = $_SESSION['teacher_id'];

try {
    $db = new Database();
    $pdo = $db->getConnection();
    
    // Get students that belong to this specific teacher
    $stmt = $pdo->prepare("SELECT * FROM students WHERE teacher_id = ? ORDER BY lastName ASC, firstName ASC");
    $stmt->execute([$teacherId]);
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format students data for frontend
    $formattedStudents = [];
    foreach ($students as $student) {
        $formattedStudents[] = [
            'id' => $student['id'],
            'firstName' => $student['firstName'] ?? '',
            'lastName' => $student['lastName'] ?? '',
            'section' => $student['grade'] . ' - ' . $student['section'], // Combine grade and section
            'lrn' => $student['lrn'], // Use actual LRN field
            'grade' => $student['grade'],
            'username' => $student['lrn'], // Use LRN as username for compatibility
            'progress' => $student['progress'] ? json_decode($student['progress'], true) : []
        ];
    }
    
    jsonResponse(true, 'Students retrieved successfully', $formattedStudents);
    
} catch (PDOException $e) {
    error_log("Error fetching students: " . $e->getMessage());
    jsonResponse(false, 'Database error: ' . $e->getMessage());
}
?>
