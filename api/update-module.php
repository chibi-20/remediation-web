<?php
// api/update-module.php - Update module endpoint
require_once '../config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'error' => 'Method not allowed'], 405);
}

// Check if admin is logged in
requireLogin();

$moduleId = $_GET['id'] ?? null;
$quarter = sanitizeInput($_POST['quarter'] ?? '');
$questions = $_POST['questions'] ?? '';

// Validate required fields
if (empty($moduleId) || empty($quarter) || empty($questions)) {
    jsonResponse(['success' => false, 'error' => 'Missing required fields']);
}

// Handle file upload (optional for updates)
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
