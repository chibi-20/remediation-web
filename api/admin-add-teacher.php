<?php
require_once '../config.php';

header('Content-Type: application/json');

// Check admin authentication
session_start();
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    jsonResponse(false, 'Unauthorized access');
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['username']) || !isset($input['password']) || !isset($input['name'])) {
        jsonResponse(false, 'Username, password, and name are required');
        exit;
    }
    
    $username = trim($input['username']);
    $password = $input['password'];
    $name = trim($input['name']);
    $email = trim($input['email'] ?? '');
    $subject = trim($input['subject'] ?? '');
    $grade = trim($input['grade'] ?? '');
    $sections = trim($input['sections'] ?? '');
    
    if (empty($username) || empty($password) || empty($name)) {
        jsonResponse(false, 'Username, password, and name are required');
        exit;
    }
    
    $db = new Database();
    $pdo = $db->getConnection();
    
    // Check if username already exists in teachers table
    $stmt = $pdo->prepare("SELECT id FROM teachers WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->fetch()) {
        jsonResponse(false, 'Username already exists');
        exit;
    }
    
    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert new teacher
    $stmt = $pdo->prepare("INSERT INTO teachers (username, password, name, email, subject, grade, sections, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
    $stmt->execute([$username, $hashedPassword, $name, $email, $subject, $grade, $sections]);
    
    jsonResponse(true, 'Teacher added successfully');
    
} catch (Exception $e) {
    error_log("Error adding teacher: " . $e->getMessage());
    jsonResponse(false, 'Error adding teacher: ' . $e->getMessage());
}
?>
