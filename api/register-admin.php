<?php
// api/register-admin.php - Admin registration endpoint
require_once '../config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'error' => 'Method not allowed'], 405);
}

$input = json_decode(file_get_contents('php://input'), true);
$name = sanitizeInput($input['name'] ?? '');
$grade = sanitizeInput($input['grade'] ?? '');
$subject = sanitizeInput($input['subject'] ?? '');
$sections = sanitizeInput($input['sections'] ?? '');
$username = sanitizeInput($input['username'] ?? '');
$password = $input['password'] ?? '';

// Validate required fields
if (empty($name) || empty($grade) || empty($subject) || empty($sections) || empty($username) || empty($password)) {
    jsonResponse(['success' => false, 'error' => 'All fields are required.']);
}

try {
    // Check if username already exists
    $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
    $stmt->execute([$username]);
    
    if ($stmt->fetch()) {
        jsonResponse(['success' => false, 'error' => 'Username already exists.']);
    }
    
    // Hash password and insert new admin
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO admins (name, grade, subject, sections, username, password) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$name, $grade, $subject, $sections, $username, $hashedPassword]);
    
    $adminId = $pdo->lastInsertId();
    
    jsonResponse(['success' => true, 'id' => $adminId]);
    
} catch (PDOException $e) {
    // Log the actual error for debugging
    error_log("Admin registration error: " . $e->getMessage());
    jsonResponse(['success' => false, 'error' => 'Database error: ' . $e->getMessage()], 500);
}
?>
