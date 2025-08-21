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
    $db = new Database();
    $pdo = $db->getConnection();
    $stmt = $pdo->prepare("
        SELECT s.id, s.username, s.name, s.section, s.grade, s.lrn, s.created_at,
               t.name as teacher_name, t.subject as teacher_subject
        FROM students s
        LEFT JOIN teachers t ON s.teacher_id = t.id
        ORDER BY s.created_at DESC
    ");
    $stmt->execute();
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    jsonResponse(true, 'Students retrieved successfully', $students);
    
} catch (Exception $e) {
    error_log("Error getting students: " . $e->getMessage());
    jsonResponse(false, 'Error retrieving students: ' . $e->getMessage());
}
?>
