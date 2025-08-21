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
    
    if (!$input || !isset($input['username']) || !isset($input['password']) || !isset($input['name']) || !isset($input['section'])) {
        jsonResponse(false, 'Username, password, name, and section are required');
        exit;
    }
    
    $username = trim($input['username']);
    $password = $input['password'];
    $name = trim($input['name']);
    $section = trim($input['section']);
    $grade = trim($input['grade'] ?? '');
    $lrn = trim($input['lrn'] ?? '');
    $teacherId = intval($input['teacher_id'] ?? 0);
    
    if (empty($username) || empty($password) || empty($name) || empty($section)) {
        jsonResponse(false, 'Username, password, name, and section are required');
        exit;
    }
    
    $db = new Database();
    $pdo = $db->getConnection();
    
    // Check if username already exists
    $stmt = $pdo->prepare("SELECT id FROM students WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->fetch()) {
        jsonResponse(false, 'Username already exists');
        exit;
    }
    
    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert new student
    $stmt = $pdo->prepare("INSERT INTO students (username, password, name, section, grade, lrn, teacher_id, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
    $stmt->execute([$username, $hashedPassword, $name, $section, $grade, $lrn, $teacherId > 0 ? $teacherId : null]);
    
    jsonResponse(true, 'Student added successfully');
    
} catch (Exception $e) {
    error_log("Error adding student: " . $e->getMessage());
    jsonResponse(false, 'Error adding student: ' . $e->getMessage());
}
?>
