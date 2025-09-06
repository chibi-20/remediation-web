<?php
// api/update-module.php - Update module for teachers
require_once '../config.php';
require_once '../secure-upload.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
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

$teacherId = $_SESSION['teacher_id'];
$moduleId = intval($_POST['id'] ?? 0);

if (!$moduleId) {
    echo json_encode(['success' => false, 'error' => 'Module ID is required']);
    exit;
}

// Validate required fields
$title = sanitizeInput($_POST['title'] ?? '');
$description = sanitizeInput($_POST['description'] ?? '');
$section = sanitizeInput($_POST['section'] ?? '');
$quarter = sanitizeInput($_POST['quarter'] ?? '');
$passingScore = intval($_POST['passingScore'] ?? 75);
$questionsJson = $_POST['questions'] ?? '';

if (empty($title) || empty($description) || empty($section) || empty($quarter)) {
    echo json_encode(['success' => false, 'error' => 'All basic fields are required']);
    exit;
}

// Validate questions
$questions = json_decode($questionsJson, true);
if (!$questions || !is_array($questions) || count($questions) === 0) {
    echo json_encode(['success' => false, 'error' => 'At least one question is required']);
    exit;
}

// Validate each question
foreach ($questions as $index => $question) {
    if (empty($question['question']) || empty($question['optionA']) || empty($question['optionB']) || 
        empty($question['optionC']) || empty($question['optionD']) || empty($question['correctAnswer'])) {
        echo json_encode(['success' => false, 'error' => "Question " . ($index + 1) . " is incomplete"]);
        exit;
    }
}

try {
    $db = new Database();
    $pdo = $db->getConnection();
    
    // Verify module exists and belongs to teacher
    $stmt = $pdo->prepare("SELECT * FROM modules WHERE id = ? AND teacher_id = ?");
    $stmt->execute([$moduleId, $teacherId]);
    $existingModule = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$existingModule) {
        echo json_encode(['success' => false, 'error' => 'Module not found or access denied']);
        exit;
    }
    
    // Handle file upload if provided
    $filename = $existingModule['filename']; // Keep existing filename by default
    
    if (isset($_FILES['pdfFile']) && $_FILES['pdfFile']['error'] === UPLOAD_ERR_OK) {
        try {
            // Delete old file if it exists
            if ($existingModule['filename']) {
                $oldFilePath = '../public/MODULES/' . $existingModule['filename'];
                if (file_exists($oldFilePath)) {
                    unlink($oldFilePath);
                }
            }
            
            // Upload new file using SecureFileUpload class
            $filename = SecureFileUpload::handleUpload('pdfFile', ['pdf']);
            
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => 'File upload failed: ' . $e->getMessage()]);
            exit;
        }
    }
    
    // Update module in database
    $stmt = $pdo->prepare("
        UPDATE modules 
        SET title = ?, description = ?, section = ?, quarter = ?, passing_score = ?, 
            filename = ?, questions = ?, updated_at = CURRENT_TIMESTAMP
        WHERE id = ? AND teacher_id = ?
    ");
    
    $result = $stmt->execute([
        $title,
        $description,
        $section,
        $quarter,
        $passingScore,
        $filename,
        $questionsJson,
        $moduleId,
        $teacherId
    ]);
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Module updated successfully',
            'data' => [
                'module_id' => $moduleId,
                'title' => $title,
                'filename' => $filename
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to update module in database']);
    }
    
} catch (PDOException $e) {
    error_log("Error updating module: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Database error occurred']);
} catch (Exception $e) {
    error_log("Error updating module: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Server error occurred']);
}
?>
