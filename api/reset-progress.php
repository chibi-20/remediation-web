<?php
// api/reset-progress.php - Reset student progress endpoint
require_once '../config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'error' => 'Method not allowed'], 405);
}

// Check authentication
startSession();
if (!isset($_SESSION['admin_id'])) {
    jsonResponse(['success' => false, 'error' => 'Authentication required'], 401);
}

$adminId = $_SESSION['admin_id'];
$input = json_decode(file_get_contents('php://input'), true);
$lrn = sanitizeInput($input['lrn'] ?? '');

// Validate required fields
if (empty($lrn)) {
    jsonResponse(['error' => 'LRN is required.'], 400);
}

try {
    // Reset progress for the specified student, but only if they belong to this teacher
    $stmt = $pdo->prepare("UPDATE students SET progress = '{}' WHERE lrn = ? AND admin_id = ?");
    $stmt->execute([$lrn, $adminId]);
    
    if ($stmt->rowCount() > 0) {
        jsonResponse(['success' => true]);
    } else {
        jsonResponse(['error' => 'Student not found or access denied.'], 404);
    }
    
} catch (PDOException $e) {
    jsonResponse(['error' => 'Failed to reset progress.'], 500);
}
?>
