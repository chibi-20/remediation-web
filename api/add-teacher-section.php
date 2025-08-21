<?php
// api/add-teacher-section.php - Add teacher to section
require_once '../config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'error' => 'Method not allowed'], 405);
}

$input = json_decode(file_get_contents('php://input'), true);
$adminId = $input['admin_id'] ?? '';
$section = sanitizeInput($input['section'] ?? '');
$role = $input['role'] ?? '';

if (!$adminId || !$section || !$role) {
    jsonResponse(['success' => false, 'error' => 'All fields are required']);
}

if (!in_array($role, ['adviser', 'subject_teacher'])) {
    jsonResponse(['success' => false, 'error' => 'Invalid role']);
}

try {
    $stmt = $pdo->prepare("INSERT INTO teacher_sections (admin_id, section, role) VALUES (?, ?, ?)");
    $stmt->execute([$adminId, $section, $role]);
    
    jsonResponse(['success' => true, 'id' => $pdo->lastInsertId()]);
    
} catch (PDOException $e) {
    if ($e->getCode() == 23000) { // Duplicate entry
        jsonResponse(['success' => false, 'error' => 'This teacher is already assigned to this section']);
    } else {
        jsonResponse(['success' => false, 'error' => 'Database error'], 500);
    }
}
?>
