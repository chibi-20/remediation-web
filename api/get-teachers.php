<?php
require_once '../config.php';

header('Content-Type: application/json');

// Check teacher authentication (no need for admin auth)
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(false, 'Method not allowed');
    exit;
}

try {
    $db = new Database();
    $pdo = $db->getConnection();
    
    // Get exclude_teacher_id from query parameters
    $excludeTeacherId = isset($_GET['exclude_teacher_id']) ? intval($_GET['exclude_teacher_id']) : null;
    
    if ($excludeTeacherId) {
        // Get all teachers except the current one
        $stmt = $pdo->prepare("SELECT id, username, name, email, subject, grade, advisory_section, sections, created_at FROM teachers WHERE id != ? ORDER BY name ASC, username ASC");
        $stmt->execute([$excludeTeacherId]);
    } else {
        // Get all teachers if no exclusion specified
        $stmt = $pdo->prepare("SELECT id, username, name, email, subject, grade, advisory_section, sections, created_at FROM teachers ORDER BY name ASC, username ASC");
        $stmt->execute();
    }
    
    $teachers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    jsonResponse(true, 'Teachers retrieved successfully', $teachers);
    
} catch (Exception $e) {
    error_log("Error getting teachers: " . $e->getMessage());
    jsonResponse(false, 'Error retrieving teachers: ' . $e->getMessage());
}
?>
