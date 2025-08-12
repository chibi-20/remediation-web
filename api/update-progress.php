<?php
// api/update-progress.php - Update student progress endpoint
require_once '../config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'error' => 'Method not allowed'], 405);
}

$input = json_decode(file_get_contents('php://input'), true);
$lrn = sanitizeInput($input['lrn'] ?? '');
$module = sanitizeInput($input['module'] ?? '');
$score = $input['score'] ?? null;

// Validate required fields
if (empty($lrn) || empty($module)) {
    jsonResponse(['error' => 'LRN and module are required.'], 400);
}

try {
    // Find student by LRN
    $stmt = $pdo->prepare("SELECT * FROM students WHERE lrn = ?");
    $stmt->execute([$lrn]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$student) {
        jsonResponse(['error' => 'Student not found.'], 404);
    }
    
    // Parse existing progress
    $progress = $student['progress'] ? json_decode($student['progress'], true) : [];
    
    // Update progress for the specified module
    $progress[$module] = $score;
    
    // Save updated progress
    $stmt = $pdo->prepare("UPDATE students SET progress = ? WHERE lrn = ?");
    $stmt->execute([json_encode($progress), $lrn]);
    
    jsonResponse(['success' => true]);
    
} catch (PDOException $e) {
    jsonResponse(['error' => 'Failed to update progress.'], 500);
}
?>
