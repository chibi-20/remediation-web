<?php
// api/student-login.php - Student login endpoint
require_once '../config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'error' => 'Method not allowed'], 405);
}

$input = json_decode(file_get_contents('php://input'), true);
$lrn = sanitizeInput($input['lrn'] ?? '');

// Validate required fields
if (empty($lrn)) {
    jsonResponse(['success' => false, 'error' => 'LRN is required.']);
}

try {
    // Find student by LRN
    $stmt = $pdo->prepare("SELECT * FROM students WHERE lrn = ?");
    $stmt->execute([$lrn]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$student) {
        jsonResponse(['success' => false, 'error' => 'Student not found. Please check your LRN.']);
    }
    
    // Start session and store student info
    startSession();
    $_SESSION['student_id'] = $student['id'];
    $_SESSION['student_lrn'] = $student['lrn'];
    $_SESSION['student_name'] = $student['firstName'] . ' ' . $student['lastName'];
    
    jsonResponse([
        'success' => true,
        'student' => [
            'id' => $student['id'],
            'firstName' => $student['firstName'],
            'lastName' => $student['lastName'],
            'section' => $student['section'],
            'lrn' => $student['lrn']
        ]
    ]);
    
} catch (PDOException $e) {
    jsonResponse(['success' => false, 'error' => 'Database error'], 500);
}
?>
