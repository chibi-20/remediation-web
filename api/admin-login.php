<?php
// api/admin-login.php - Admin login endpoint
require_once '../config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'error' => 'Method not allowed'], 405);
}

$input = json_decode(file_get_contents('php://input'), true);
$username = sanitizeInput($input['username'] ?? '');
$password = $input['password'] ?? '';

// Validate required fields
if (empty($username) || empty($password)) {
    jsonResponse(['success' => false, 'error' => 'All fields are required.']);
}

try {
    // Find admin by username
    $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
    $stmt->execute([$username]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$admin || !password_verify($password, $admin['password'])) {
        jsonResponse(['success' => false, 'error' => 'Invalid credentials']);
    }
    
    // Start session and store admin info
    startSession();
    $_SESSION['admin_id'] = $admin['id'];
    $_SESSION['username'] = $admin['username'];
    
    jsonResponse(['success' => true]);
    
} catch (PDOException $e) {
    jsonResponse(['success' => false, 'error' => 'Database error'], 500);
}
?>
