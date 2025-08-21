<?php
// api/remove-teacher-section.php - Remove teacher-section assignment
require_once '../config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'error' => 'Method not allowed'], 405);
}

$input = json_decode(file_get_contents('php://input'), true);
$id = $input['id'] ?? '';

if (!$id) {
    jsonResponse(['success' => false, 'error' => 'Assignment ID is required']);
}

try {
    $stmt = $pdo->prepare("DELETE FROM teacher_sections WHERE id = ?");
    $stmt->execute([$id]);
    
    if ($stmt->rowCount() > 0) {
        jsonResponse(['success' => true]);
    } else {
        jsonResponse(['success' => false, 'error' => 'Assignment not found']);
    }
    
} catch (PDOException $e) {
    jsonResponse(['success' => false, 'error' => 'Database error'], 500);
}
?>
