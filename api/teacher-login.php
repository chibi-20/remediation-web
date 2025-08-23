<?php
require_once '../config.php';
require_once '../security-middleware.php';

// Apply login security checks
SecurityMiddleware::checkLoginSecurity();

header('Content-Type: application/json');

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['username']) || !isset($input['password'])) {
        jsonResponse(false, 'Username and password are required');
        exit;
    }
    
    $username = trim($input['username']);
    $password = $input['password'];
    
    $db = new Database();
    $pdo = $db->getConnection();
    
    // Check in teachers table
    $stmt = $pdo->prepare("SELECT * FROM teachers WHERE username = ?");
    $stmt->execute([$username]);
    $teacher = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$teacher) {
        SecurityMiddleware::logSecurityEvent('failed_login_attempt', [
            'type' => 'teacher',
            'username' => $username,
            'reason' => 'invalid_username'
        ]);
        jsonResponse(false, 'Invalid username or password');
        exit;
    }
    
    // Verify password
    if (!password_verify($password, $teacher['password'])) {
        SecurityMiddleware::logSecurityEvent('failed_login_attempt', [
            'type' => 'teacher',
            'username' => $username,
            'reason' => 'invalid_password'
        ]);
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
    
    // Log successful login
    SecurityMiddleware::logSecurityEvent('successful_login', [
        'type' => 'teacher',
        'username' => $username,
        'user_id' => $teacher['id']
    ]);
    
    // Record login request for rate limiting
    RateLimiter::recordRequest('login');
    
    jsonResponse(true, 'Login successful', [
        'token' => $token,
        'teacher' => [
            'id' => $teacher['id'],
            'username' => $teacher['username'],
            'name' => $teacher['name'],
            'email' => $teacher['email'],
            'grade' => $teacher['grade'],
            'subject' => $teacher['subject'],
            'advisory_section' => $teacher['advisory_section'],
            'sections' => $teacher['sections']
        ]
    ]);
    
} catch (Exception $e) {
    error_log("Teacher login error: " . $e->getMessage());
    jsonResponse(false, 'Login failed: ' . $e->getMessage());
}
?>
