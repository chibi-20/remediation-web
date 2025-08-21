<?php
// api/teacher-sections-list.php - List all teacher-section assignments
require_once '../config.php';

header('Content-Type: application/json');

try {
    $stmt = $pdo->prepare("
        SELECT ts.*, a.name as teacher_name, a.subject 
        FROM teacher_sections ts 
        JOIN admins a ON ts.admin_id = a.id 
        ORDER BY ts.section, a.name
    ");
    $stmt->execute();
    $assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    jsonResponse($assignments);
    
} catch (PDOException $e) {
    jsonResponse(['error' => 'Database error'], 500);
}
?>
