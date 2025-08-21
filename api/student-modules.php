<?php
// api/student-modules.php - Get modules for student based on section teachers
require_once '../config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(['success' => false, 'error' => 'Method not allowed'], 405);
}

$lrn = $_GET['lrn'] ?? '';

if (!$lrn) {
    jsonResponse(['success' => false, 'error' => 'LRN is required'], 400);
}

try {
    // Get student information
    $stmt = $pdo->prepare("SELECT * FROM students WHERE lrn = ?");
    $stmt->execute([$lrn]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$student) {
        jsonResponse(['success' => false, 'error' => 'Student not found'], 404);
    }
    
    // Get all teachers assigned to this student's section
    $stmt = $pdo->prepare("
        SELECT DISTINCT ts.admin_id 
        FROM teacher_sections ts 
        WHERE ts.section = ?
    ");
    $stmt->execute([$student['section']]);
    $teacherIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // If no teachers found in teacher_sections, fallback to student's direct admin_id
    if (empty($teacherIds) && $student['admin_id']) {
        $teacherIds = [$student['admin_id']];
    }
    
    if (empty($teacherIds)) {
        jsonResponse(['success' => true, 'modules' => [], 'student' => $student]);
    }
    
    // Get modules from ALL teachers assigned to this section
    $placeholders = str_repeat('?,', count($teacherIds) - 1) . '?';
    $stmt = $pdo->prepare("
        SELECT m.*, a.name as teacher_name 
        FROM modules m 
        JOIN admins a ON m.admin_id = a.id 
        WHERE m.admin_id IN ($placeholders) 
        ORDER BY m.quarter, m.id
    ");
    $stmt->execute($teacherIds);
    $modules = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Parse questions JSON for each module
    foreach ($modules as &$module) {
        $module['questions'] = $module['questions'] ? json_decode($module['questions'], true) : [];
    }
    
    // Parse student progress
    $student['progress'] = $student['progress'] ? json_decode($student['progress'], true) : [];
    
    jsonResponse([
        'success' => true,
        'modules' => $modules,
        'student' => $student,
        'teacher_ids' => $teacherIds
    ]);
    
} catch (PDOException $e) {
    jsonResponse(['success' => false, 'error' => 'Database error'], 500);
}
?>
