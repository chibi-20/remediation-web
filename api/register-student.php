<?php
// api/register-student.php - Student registration endpoint
require_once '../config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'error' => 'Method not allowed'], 405);
}

$input = json_decode(file_get_contents('php://input'), true);
$firstName = sanitizeInput($input['firstName'] ?? '');
$lastName = sanitizeInput($input['lastName'] ?? '');
$section = sanitizeInput($input['section'] ?? '');
$lrn = sanitizeInput($input['lrn'] ?? '');
$adminId = $input['admin_id'] ?? null;

// Validate required fields
$error = validateRequired(['firstName', 'lastName', 'section', 'lrn'], $input);
if ($error) {
    jsonResponse(['success' => false, 'error' => $error]);
}

// Validate LRN format (should be 12 digits)
if (!preg_match('/^\d{12}$/', $lrn)) {
    jsonResponse(['success' => false, 'error' => 'LRN must be exactly 12 digits.']);
}

try {
    // Check if LRN already exists
    $stmt = $pdo->prepare("SELECT * FROM students WHERE lrn = ?");
    $stmt->execute([$lrn]);
    
    if ($stmt->fetch()) {
        jsonResponse(['success' => false, 'error' => 'LRN already exists.']);
    }
    
    // Insert new student
    $stmt = $pdo->prepare("INSERT INTO students (firstName, lastName, section, lrn, admin_id) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$firstName, $lastName, $section, $lrn, $adminId]);
    
    $studentId = $pdo->lastInsertId();
    
    jsonResponse(['success' => true, 'id' => $studentId]);
    
} catch (PDOException $e) {
    jsonResponse(['success' => false, 'error' => 'Failed to register student.'], 500);
}
?>
