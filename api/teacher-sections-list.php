<?php
// api/teacher-sections-list.php - List all teacher-section assignments
require_once '../config.php';

header('Content-Type: application/json');

try {
    $db = new Database();
    $pdo = $db->getConnection();
    
    // Get teacher-section assignments with teacher details
    $stmt = $pdo->query("
        SELECT 
            ts.id,
            ts.section,
            ts.role,
            ts.created_at,
            t.id as teacher_id,
            t.username as teacher_username,
            t.name as teacher_name,
            t.subject as teacher_subject,
            t.grade as teacher_grade
        FROM teacher_sections ts
        LEFT JOIN teachers t ON ts.admin_id = t.id
        ORDER BY ts.section, ts.role DESC, t.name
    ");
    
    $assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Return as JSON array (maintaining backward compatibility)
    echo json_encode($assignments);
    
} catch (Exception $e) {
    error_log("Error getting teacher sections: " . $e->getMessage());
    echo json_encode([]);
}
?>
