<?php
// api-router.php - Simple API router
header('Content-Type: application/json');

$request = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];

// Get the last part of the URL
$pathParts = explode('/', trim($request, '/'));
$apiFile = end($pathParts);

// Remove .php extension if present
$apiFile = str_replace('.php', '', $apiFile);

switch ($apiFile) {
    case 'register-admin':
        if ($method === 'POST') {
            require_once '../config.php';
            
            $input = json_decode(file_get_contents('php://input'), true);
            $name = sanitizeInput($input['name'] ?? '');
            $grade = sanitizeInput($input['grade'] ?? '');
            $subject = sanitizeInput($input['subject'] ?? '');
            $username = sanitizeInput($input['username'] ?? '');
            $password = $input['password'] ?? '';

            if (empty($username) || empty($password)) {
                jsonResponse(['success' => false, 'error' => 'Username and password are required.']);
            }

            try {
                $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
                $stmt->execute([$username]);
                
                if ($stmt->fetch()) {
                    jsonResponse(['success' => false, 'error' => 'Username already exists.']);
                }
                
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO admins (name, grade, subject, username, password) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$name, $grade, $subject, $username, $hashedPassword]);
                
                $adminId = $pdo->lastInsertId();
                jsonResponse(['success' => true, 'id' => $adminId]);
                
            } catch (PDOException $e) {
                jsonResponse(['success' => false, 'error' => 'Failed to register.'], 500);
            }
        }
        break;
        
    default:
        jsonResponse(['success' => false, 'error' => 'API endpoint not found'], 404);
}
?>
