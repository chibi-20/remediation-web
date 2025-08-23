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
    
    // Check if this is the master admin account
    if ($username === '307901' && $password === 'ilovejacobo') {
        // Generate a simple token
        $token = bin2hex(random_bytes(32));
        
        // Store admin session
        session_start();
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = $username;
        $_SESSION['admin_token'] = $token;
        
        // Log successful login
        SecurityMiddleware::logSecurityEvent('successful_login', [
            'type' => 'admin',
            'username' => $username,
            'user_id' => 1,
            'role' => 'super_admin'
        ]);
        
        // Record login request for rate limiting
        RateLimiter::recordRequest('login');
        
        jsonResponse(true, 'Login successful', [
            'token' => $token,
            'admin' => [
                'id' => 1,
                'username' => $username,
                'role' => 'super_admin'
            ]
        ]);
        exit;
    }
    
    // Check in admins table for other admin accounts
    $db = new Database();
    $pdo = $db->getConnection();
    $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
    $stmt->execute([$username]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$admin) {
        SecurityMiddleware::logSecurityEvent('failed_login_attempt', [
            'type' => 'admin',
            'username' => $username,
            'reason' => 'invalid_username'
        ]);
        jsonResponse(false, 'Invalid username or password');
        exit;
    }
    
    // Verify password
    if (!password_verify($password, $admin['password'])) {
        SecurityMiddleware::logSecurityEvent('failed_login_attempt', [
            'type' => 'admin',
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
    $_SESSION['admin_logged_in'] = true;
    $_SESSION['admin_id'] = $admin['id'];
    $_SESSION['admin_username'] = $admin['username'];
    $_SESSION['admin_token'] = $token;
    
    // Log successful login
    SecurityMiddleware::logSecurityEvent('successful_login', [
        'type' => 'admin',
        'username' => $username,
        'user_id' => $admin['id']
    ]);
    
    // Record login request for rate limiting
    RateLimiter::recordRequest('login');
    
    jsonResponse(true, 'Login successful', [
        'token' => $token,
        'admin' => [
            'id' => $admin['id'],
            'username' => $admin['username'],
            'role' => 'admin'
        ]
    ]);
    
} catch (Exception $e) {
    error_log("Admin login error: " . $e->getMessage());
    jsonResponse(false, 'Login failed: ' . $e->getMessage());
}
?>
