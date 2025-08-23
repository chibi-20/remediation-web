<?php
// api/update-module.php - Update module endpoint
require_once '../config.php';
require_once '../secure-upload.php';
require_once '../security-middleware.php';

// Apply upload security checks
SecurityMiddleware::checkUploadSecurity();

header('Content-Type: application/json');

// Check if admin is logged in
requireLogin();

$moduleId = $_GET['id'] ?? null;
$quarter = sanitizeInput($_POST['quarter'] ?? '');
$questions = $_POST['questions'] ?? '';

// Validate required fields
if (empty($moduleId) || empty($quarter) || empty($questions)) {
    jsonResponse(['success' => false, 'error' => 'Missing required fields']);
}

// Handle secure file upload (optional for updates)
$filename = null;
try {
    if (isset($_FILES['pdf']) && $_FILES['pdf']['error'] === UPLOAD_ERR_OK) {
        $filename = SecureFileUpload::handleUpload('pdf', ['pdf']);
    }
} catch (Exception $e) {
    jsonResponse(['success' => false, 'error' => 'File upload error: ' . $e->getMessage()]);
}

try {
    if ($filename) {
        // Update with new file
        $stmt = $pdo->prepare("UPDATE modules SET quarter = ?, filename = ?, questions = ? WHERE id = ?");
        $stmt->execute([$quarter, $filename, $questions, $moduleId]);
    } else {
        // Update without new file
        $stmt = $pdo->prepare("UPDATE modules SET quarter = ?, questions = ? WHERE id = ?");
        $stmt->execute([$quarter, $questions, $moduleId]);
    }
    
    jsonResponse(['success' => true]);
    
} catch (PDOException $e) {
    jsonResponse(['success' => false, 'error' => 'Database error'], 500);
}
?>
