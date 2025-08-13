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
$adminId = $input['adminId'] ?? null;
$password = $input['password'] ?? '';

// Validate password
if (strlen($password) < 6) {
    jsonResponse(['success' => false, 'error' => 'Password must be at least 6 characters.']);
}

$passwordHash = password_hash($password, PASSWORD_DEFAULT);

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
    
    // Insert new student with password
    $stmt = $pdo->prepare("INSERT INTO students (firstName, lastName, section, lrn, password, admin_id) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$firstName, $lastName, $section, $lrn, $passwordHash, $adminId]);
    
    $studentId = $pdo->lastInsertId();
    
    jsonResponse(['success' => true, 'id' => $studentId]);
    
} catch (PDOException $e) {
    jsonResponse(['success' => false, 'error' => 'Failed to register student.'], 500);
}
?>
