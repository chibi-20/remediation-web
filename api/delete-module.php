<?php
// api/delete-module.php - Delete module endpoint for teachers
require_once '../config.php';

// CORS headers for API access
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Check teacher authentication
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['teacher_logged_in']) || !$_SESSION['teacher_logged_in'] || !isset($_SESSION['teacher_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Teacher authentication required']);
    exit;
}

$teacher_id = $_SESSION['teacher_id'];

try {
    // Get module ID from query parameter or request body
    $module_id = null;
    if (isset($_GET['id'])) {
        $module_id = $_GET['id'];
    } else {
        $input = json_decode(file_get_contents('php://input'), true);
        if (isset($input['id'])) {
            $module_id = $input['id'];
        }
    }
    
    if (!$module_id) {
        echo json_encode(['error' => 'Module ID is required']);
        exit;
    }
    
    // First, verify the module exists and belongs to this teacher
    $stmt = $pdo->prepare("SELECT * FROM modules WHERE id = ? AND teacher_id = ?");
    $stmt->execute([$module_id, $teacher_id]);
    $module = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$module) {
        echo json_encode(['error' => 'Module not found or access denied']);
        exit;
    }
    
    // Delete the module file if it exists
    if (!empty($module['file_path'])) {
        $file_path = '../' . $module['file_path'];
        if (file_exists($file_path)) {
            unlink($file_path);
        }
    }
    
    // Delete from database
    $stmt = $pdo->prepare("DELETE FROM modules WHERE id = ? AND teacher_id = ?");
    $result = $stmt->execute([$module_id, $teacher_id]);
    
    if ($result) {
        echo json_encode([
            'success' => true, 
            'message' => 'Module deleted successfully'
        ]);
    } else {
        echo json_encode(['error' => 'Failed to delete module']);
    }
    
} catch (Exception $e) {
    error_log("Delete module error: " . $e->getMessage());
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}
?>
