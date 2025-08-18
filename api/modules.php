<?php
// api/modules.php - Get modules for authenticated teacher
require_once '../config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(['success' => false, 'error' => 'Method not allowed'], 405);
}

// Check authentication
startSession();
if (!isset($_SESSION['admin_id'])) {
    jsonResponse(['success' => false, 'error' => 'Authentication required'], 401);
}

$adminId = $_SESSION['admin_id'];

try {
    // Only get modules that belong to this specific teacher (admin)
    $stmt = $pdo->prepare("SELECT * FROM modules WHERE admin_id = ?");
    $stmt->execute([$adminId]);
    $modules = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Parse questions JSON for each module
    foreach ($modules as &$module) {
        $module['questions'] = $module['questions'] ? json_decode($module['questions'], true) : [];
    }
    
    jsonResponse($modules);
    
} catch (PDOException $e) {
    jsonResponse(['error' => 'Database error'], 500);
}
?>
