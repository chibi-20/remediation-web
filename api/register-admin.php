<?php
// api/register-admin.php - Admin registration endpoint
require_once '../config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Method not allowed');
}

$input = json_decode(file_get_contents('php://input'), true);

// Add debugging
error_log("Registration input: " . json_encode($input));

$name = sanitizeInput($input['name'] ?? '');
$grade = sanitizeInput($input['grade'] ?? '');
$subject = sanitizeInput($input['subject'] ?? '');
$advisory_section = sanitizeInput($input['advisory_section'] ?? '');
$sections = sanitizeInput($input['sections'] ?? '');
$username = sanitizeInput($input['username'] ?? '');
$password = $input['password'] ?? '';

// Check if this is teacher registration (has subject/grade) vs admin registration
$isTeacher = !empty($subject) && !empty($grade);

// Validate required fields
if (empty($name) || empty($username) || empty($password)) {
    jsonResponse(false, 'Name, username, and password are required.');
}

if ($isTeacher && (empty($grade) || empty($subject))) {
    jsonResponse(false, 'Grade and subject are required for teacher registration.');
}

try {
    $db = new Database();
    $pdo = $db->getConnection();
    
    if ($isTeacher) {
        // Teacher registration
        // Check if username already exists in teachers table
        $stmt = $pdo->prepare("SELECT * FROM teachers WHERE username = ?");
        $stmt->execute([$username]);
        
        if ($stmt->fetch()) {
            jsonResponse(false, 'Username already exists.');
        }
        
        // Hash password and insert new teacher
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO teachers (name, grade, subject, advisory_section, sections, username, password, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
        $stmt->execute([$name, $grade, $subject, $advisory_section, $sections ?: '', $username, $hashedPassword]);
        
        $teacherId = $pdo->lastInsertId();
        jsonResponse(true, 'Teacher registration successful', ['id' => $teacherId, 'type' => 'teacher']);
        
    } else {
        // Admin registration
        // Check if username already exists in admins table
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
        $stmt->execute([$username]);
        
        if ($stmt->fetch()) {
            jsonResponse(false, 'Username already exists.');
        }
        
        // Hash password and insert new admin
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO admins (name, grade, subject, sections, username, password) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $grade, $subject, $sections, $username, $hashedPassword]);
        
        $adminId = $pdo->lastInsertId();
        jsonResponse(true, 'Admin registration successful', ['id' => $adminId, 'type' => 'admin']);
    }
    
} catch (PDOException $e) {
    // Log the actual error for debugging
    error_log("Registration error: " . $e->getMessage());
    jsonResponse(false, 'Database error: ' . $e->getMessage());
}
?>
