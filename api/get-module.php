<?php
// api/get-module.php - Get single module details for editing
require_once '../config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// Check teacher authentication
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['teacher_logged_in']) || !$_SESSION['teacher_logged_in'] || !isset($_SESSION['teacher_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Teacher authentication required']);
    exit;
}

$moduleId = intval($_GET['id'] ?? 0);
$teacherId = $_SESSION['teacher_id'];

if (!$moduleId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Module ID is required']);
    exit;
}

try {
    $db = new Database();
    $pdo = $db->getConnection();
    
    // Get module details - only if it belongs to this teacher
    $stmt = $pdo->prepare("SELECT * FROM modules WHERE id = ? AND teacher_id = ?");
    $stmt->execute([$moduleId, $teacherId]);
    $module = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$module) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Module not found or access denied']);
        exit;
    }
    
    // Parse questions JSON
    if ($module['questions']) {
        $module['questions'] = json_decode($module['questions'], true);
    } else {
        $module['questions'] = [];
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Module retrieved successfully',
        'data' => $module
    ]);
    
} catch (PDOException $e) {
    error_log("Error fetching module: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error']);
} catch (Exception $e) {
    error_log("Error fetching module: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error']);
}
?>
