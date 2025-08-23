<?php
require_once '../config.php';

header('Content-Type: application/json');

// Check teacher authentication
session_start();
if (!isset($_SESSION['teacher_logged_in']) || !$_SESSION['teacher_logged_in']) {
    jsonResponse(false, 'Unauthorized access');
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
        error_log("Update teacher sections input: " . print_r($input, true));
    
    if (!$input) {
        jsonResponse(false, 'Invalid JSON input');
        exit;
    }
    
    if (!isset($input['teacher_id'])) {
        jsonResponse(false, 'Teacher ID is required');
        exit;
    }
    
    if (!array_key_exists('sections', $input)) {
        jsonResponse(false, 'Sections parameter is required');
        exit;
    }
    
    $teacherId = intval($input['teacher_id']);
    $sections = isset($input['sections']) ? trim($input['sections']) : '';
    
            
    if ($teacherId <= 0) {
        jsonResponse(false, 'Invalid teacher ID');
        exit;
    }
    
    // Verify this is the logged-in teacher
    if ($teacherId !== $_SESSION['teacher_id']) {
        jsonResponse(false, 'Unauthorized: Can only update your own sections');
        exit;
    }
    
    $db = new Database();
    $pdo = $db->getConnection();
    
    // Update teacher sections
    $stmt = $pdo->prepare("UPDATE teachers SET sections = ? WHERE id = ?");
    $stmt->execute([$sections, $teacherId]);
    
    if ($stmt->rowCount() > 0) {
        jsonResponse(true, 'Sections updated successfully', [
            'teacher_id' => $teacherId,
            'sections' => $sections
        ]);
    } else {
        jsonResponse(false, 'No changes made or teacher not found');
    }
    
} catch (Exception $e) {
    error_log("Update teacher sections error: " . $e->getMessage());
    jsonResponse(false, 'Server error: ' . $e->getMessage());
}
?>
