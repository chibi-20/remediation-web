<?php
// api/modules.php - Get modules for authenticated teacher
require_once '../config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(false, 'Method not allowed', null, 405);
}

// Check teacher authentication
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['teacher_logged_in']) || !$_SESSION['teacher_logged_in'] || !isset($_SESSION['teacher_id'])) {
    jsonResponse(false, 'Teacher authentication required', null, 401);
}

$teacherId = $_SESSION['teacher_id'];

try {
    $db = new Database();
    $pdo = $db->getConnection();
    
    // Get modules directly using teacher_id (no need for admin lookup anymore)
    $stmt = $pdo->prepare("SELECT * FROM modules WHERE teacher_id = ? ORDER BY created_at DESC");
    $stmt->execute([$teacherId]);
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
