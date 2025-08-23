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
    $stmt = $pdo->prepare("SELECT id, username, name, email, subject, grade, advisory_section, sections, created_at FROM teachers ORDER BY created_at DESC");
    $stmt->execute();
    $teachers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    jsonResponse(true, 'Teachers retrieved successfully', $teachers);
    
} catch (Exception $e) {
    error_log("Error getting teachers: " . $e->getMessage());
    jsonResponse(false, 'Error retrieving teachers: ' . $e->getMessage());
}
?>
