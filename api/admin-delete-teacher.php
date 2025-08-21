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
        jsonResponse(false, 'Teacher ID is required');
        exit;
    }
    
    $teacherId = intval($input['id']);
    
    if ($teacherId <= 0) {
        jsonResponse(false, 'Invalid teacher ID');
        exit;
    }
    
    $db = Database::getInstance();
    
    // Check if teacher exists
    $stmt = $db->prepare("SELECT id FROM teachers WHERE id = ?");
    $stmt->execute([$teacherId]);
    if (!$stmt->fetch()) {
        jsonResponse(false, 'Teacher not found');
        exit;
    }
    
    // Delete teacher
    $stmt = $db->prepare("DELETE FROM teachers WHERE id = ?");
    $stmt->execute([$teacherId]);
    
    jsonResponse(true, 'Teacher deleted successfully');
    
} catch (Exception $e) {
    error_log("Error deleting teacher: " . $e->getMessage());
    jsonResponse(false, 'Error deleting teacher: ' . $e->getMessage());
}
?>
