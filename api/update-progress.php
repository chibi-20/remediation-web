<?php
// api/update-progress.php - Update student progress endpoint
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
$module = sanitizeInput($input['module'] ?? '');
$score = $input['score'] ?? null;

// Validate required fields
if (empty($lrn) || empty($module)) {
    jsonResponse(['error' => 'LRN and module are required.'], 400);
}

try {
    // Find student by LRN AND verify they belong to this teacher
    $stmt = $pdo->prepare("SELECT * FROM students WHERE lrn = ? AND admin_id = ?");
    $stmt->execute([$lrn, $adminId]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$student) {
        jsonResponse(['error' => 'Student not found or access denied.'], 404);
    }
    
    // Parse existing progress
    $progress = $student['progress'] ? json_decode($student['progress'], true) : [];
    
    // Update progress for the specified module
    $progress[$module] = $score;
    
    // Save updated progress
    $stmt = $pdo->prepare("UPDATE students SET progress = ? WHERE lrn = ? AND admin_id = ?");
    $stmt->execute([json_encode($progress), $lrn, $adminId]);
    
    jsonResponse(['success' => true]);
    
} catch (PDOException $e) {
    jsonResponse(['error' => 'Failed to update progress.'], 500);
}
?>
