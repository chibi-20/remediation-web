<?php
// api/modules.php - Get all modules endpoint
require_once '../config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(['success' => false, 'error' => 'Method not allowed'], 405);
}

try {
    $stmt = $pdo->prepare("SELECT * FROM modules");
    $stmt->execute();
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
