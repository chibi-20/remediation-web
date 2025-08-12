<?php
// api/create-module.php - Create module endpoint
require_once '../config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'error' => 'Method not allowed'], 405);
}

// Check if admin is logged in
requireLogin();

$quarter = sanitizeInput($_POST['quarter'] ?? '');
$questions = $_POST['questions'] ?? '';
$adminId = $_SESSION['admin_id'];

// Validate required fields
if (empty($quarter) || empty($questions)) {
    jsonResponse(['success' => false, 'error' => 'Missing required fields']);
}

// Handle file upload
$filename = null;
if (isset($_FILES['pdf']) && $_FILES['pdf']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = __DIR__ . '/../public/modules/';
    
    // Create directory if it doesn't exist
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    $originalName = $_FILES['pdf']['name'];
    $uniqueName = time() . '-' . $originalName;
    $uploadPath = $uploadDir . $uniqueName;
    
    if (move_uploaded_file($_FILES['pdf']['tmp_name'], $uploadPath)) {
        $filename = $uniqueName;
    } else {
        jsonResponse(['success' => false, 'error' => 'File upload failed']);
    }
}

if (!$filename) {
    jsonResponse(['success' => false, 'error' => 'PDF file is required']);
}

try {
    $stmt = $pdo->prepare("INSERT INTO modules (quarter, filename, questions, admin_id) VALUES (?, ?, ?, ?)");
    $stmt->execute([$quarter, $filename, $questions, $adminId]);
    
    jsonResponse(['success' => true]);
    
} catch (PDOException $e) {
    jsonResponse(['success' => false, 'error' => 'Database error'], 500);
}
?>
