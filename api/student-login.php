<?php
// api/student-login.php - Student login endpoint
require_once '../config.php';
require_once '../security-middleware.php';

// Apply login security checks
SecurityMiddleware::checkLoginSecurity();

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$lrn = sanitizeInput($input['lrn'] ?? '');
$password = $input['password'] ?? '';

// Validate required fields
if (empty($lrn) || empty($password)) {
    echo json_encode(['success' => false, 'error' => 'LRN and password are required.']);
    exit;
}

try {
    $db = new Database();
    $pdo = $db->getConnection();
    
    // Find student by LRN
    $stmt = $pdo->prepare("SELECT * FROM students WHERE lrn = ?");
    $stmt->execute([$lrn]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$student || empty($student['password'])) {
        SecurityMiddleware::logSecurityEvent('failed_login_attempt', [
            'type' => 'student',
            'lrn' => $lrn,
            'reason' => 'invalid_lrn'
        ]);
        echo json_encode(['success' => false, 'error' => 'Invalid LRN or password.']);
        exit;
    }
    
    if (!password_verify($password, $student['password'])) {
        SecurityMiddleware::logSecurityEvent('failed_login_attempt', [
            'type' => 'student',
            'lrn' => $lrn,
            'reason' => 'invalid_password'
        ]);
        echo json_encode(['success' => false, 'error' => 'Invalid LRN or password.']);
        exit;
    }
    
    // Start session and store student info
    session_start();
    $_SESSION['student_id'] = $student['id'];
    $_SESSION['student_lrn'] = $student['lrn'];
    $_SESSION['student_name'] = $student['firstName'] . ' ' . $student['lastName'];
    
    // Log successful login
    SecurityMiddleware::logSecurityEvent('successful_login', [
        'type' => 'student',
        'lrn' => $lrn,
        'user_id' => $student['id']
    ]);
    
    // Record login request for rate limiting
    RateLimiter::recordRequest('login');
    
    echo json_encode([
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
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
?>
