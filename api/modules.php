<?php
// api/modules.php - Get modules for authenticated teacher
require_once '../config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(false, 'Method not allowed', null, 405);
}

// Check teacher authentication
session_start();
if (!isset($_SESSION['teacher_logged_in']) || !$_SESSION['teacher_logged_in'] || !isset($_SESSION['teacher_id'])) {
    jsonResponse(false, 'Teacher authentication required', null, 401);
}

$teacherId = $_SESSION['teacher_id'];

try {
    $db = new Database();
    $pdo = $db->getConnection();
    
    // Get the teacher's information to find their admin_id
    $stmt = $pdo->prepare("SELECT username FROM teachers WHERE id = ?");
    $stmt->execute([$teacherId]);
    $teacher = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$teacher) {
        jsonResponse(false, 'Teacher not found', null, 404);
    }
    
    // Find the corresponding admin_id for this teacher
    $stmt = $pdo->prepare("SELECT id FROM admins WHERE username = ?");
    $stmt->execute([$teacher['username']]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$admin) {
        jsonResponse(false, 'Teacher admin record not found', null, 404);
    }
    
    // Get only modules created by this specific teacher
    $stmt = $pdo->prepare("SELECT * FROM modules WHERE admin_id = ? ORDER BY created_at DESC");
    $stmt->execute([$admin['id']]);
    $modules = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Parse questions JSON for each module
    foreach ($modules as &$module) {
        $module['questions'] = $module['questions'] ? json_decode($module['questions'], true) : [];
    }
    
    jsonResponse(true, 'Modules retrieved successfully', $modules);
    
} catch (PDOException $e) {
    error_log("Error fetching modules: " . $e->getMessage());
    jsonResponse(false, 'Database error: ' . $e->getMessage(), null, 500);
}
?>
