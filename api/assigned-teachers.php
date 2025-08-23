<?php
require_once '../config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Get assigned teachers for a section
    try {
        $section = $_GET['section'] ?? '';
        
        if (empty($section)) {
            jsonResponse(false, 'Section parameter is required');
            exit;
        }
        
        $db = new Database();
        $pdo = $db->getConnection();
        
        // Get teachers assigned to this section
        $stmt = $pdo->prepare("
            SELECT t.id, t.name, t.username, t.subject, t.grade, t.email, ts.created_at as assigned_at
            FROM teacher_sections ts 
            JOIN teachers t ON ts.admin_id = t.id 
            WHERE ts.section = ? AND ts.role = 'subject_teacher'
            ORDER BY t.name ASC, t.username ASC
        ");
        $stmt->execute([$section]);
        $assignedTeachers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        jsonResponse(true, 'Assigned teachers retrieved successfully', $assignedTeachers);
        
    } catch (Exception $e) {
        error_log("Error getting assigned teachers: " . $e->getMessage());
        jsonResponse(false, 'Error retrieving assigned teachers: ' . $e->getMessage());
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    // Remove teacher assignment
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        $teacherId = $input['teacher_id'] ?? null;
        $section = $input['section'] ?? '';
        
        if (!$teacherId || empty($section)) {
            jsonResponse(false, 'Teacher ID and section are required');
            exit;
        }
        
        $db = new Database();
        $pdo = $db->getConnection();
        
        $stmt = $pdo->prepare("DELETE FROM teacher_sections WHERE admin_id = ? AND section = ? AND role = 'subject_teacher'");
        $stmt->execute([$teacherId, $section]);
        
        if ($stmt->rowCount() > 0) {
            jsonResponse(true, 'Teacher assignment removed successfully');
        } else {
            jsonResponse(false, 'Assignment not found or already removed');
        }
        
    } catch (Exception $e) {
        error_log("Error removing teacher assignment: " . $e->getMessage());
        jsonResponse(false, 'Error removing assignment: ' . $e->getMessage());
    }
} else {
    jsonResponse(false, 'Method not allowed');
}
?>
