<?php
require_once '../config.php';

header('Content-Type: application/json');

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['username']) || !isset($input['password'])) {
        jsonResponse(false, 'Username and password are required');
        exit;
    }
    
    $username = trim($input['username']);
    $password = $input['password'];
    
    $db = Database::getInstance();
    
    // Check in teachers table
    $stmt = $db->prepare("SELECT * FROM teachers WHERE username = ?");
    $stmt->execute([$username]);
    $teacher = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$teacher) {
        jsonResponse(false, 'Invalid username or password');
        exit;
    }
    
    // Verify password
    if (!password_verify($password, $teacher['password'])) {
        jsonResponse(false, 'Invalid username or password');
        exit;
    }
    
    // Generate token
    $token = bin2hex(random_bytes(32));
    
    // Start session
    session_start();
    $_SESSION['teacher_logged_in'] = true;
    $_SESSION['teacher_id'] = $teacher['id'];
    $_SESSION['teacher_username'] = $teacher['username'];
    $_SESSION['teacher_name'] = $teacher['name'];
    $_SESSION['teacher_token'] = $token;
    
    jsonResponse(true, 'Login successful', [
        'token' => $token,
        'teacher' => [
            'id' => $teacher['id'],
            'username' => $teacher['username'],
            'name' => $teacher['name'],
            'email' => $teacher['email'],
            'subject' => $teacher['subject']
        ]
    ]);
    
} catch (Exception $e) {
    error_log("Teacher login error: " . $e->getMessage());
    jsonResponse(false, 'Login failed: ' . $e->getMessage());
}
?>
