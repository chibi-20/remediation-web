<?php
require_once '../config.php';

header('Content-Type: application/json');

// Check admin authentication
session_start();
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    jsonResponse(false, 'Unauthorized access');
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['id'])) {
        jsonResponse(false, 'Student ID is required');
        exit;
    }
    
    $studentId = intval($input['id']);
    
    if ($studentId <= 0) {
        jsonResponse(false, 'Invalid student ID');
        exit;
    }
    
    $db = Database::getInstance();
    
    // Check if student exists
    $stmt = $db->prepare("SELECT id FROM students WHERE id = ?");
    $stmt->execute([$studentId]);
    if (!$stmt->fetch()) {
        jsonResponse(false, 'Student not found');
        exit;
    }
    
    // Delete student
    $stmt = $db->prepare("DELETE FROM students WHERE id = ?");
    $stmt->execute([$studentId]);
    
    jsonResponse(true, 'Student deleted successfully');
    
} catch (Exception $e) {
    error_log("Error deleting student: " . $e->getMessage());
    jsonResponse(false, 'Error deleting student: ' . $e->getMessage());
}
?>
