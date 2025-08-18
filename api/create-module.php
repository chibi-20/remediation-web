<?php
// api/create-module.php - Create module endpoint
require_once '../config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'error' => 'Method not allowed'], 405);
}

// Check if admin is logged in
requireLogin();

$title = sanitizeInput($_POST['title'] ?? '');
$description = sanitizeInput($_POST['description'] ?? '');
$section = sanitizeInput($_POST['section'] ?? '');
$passingScore = intval($_POST['passingScore'] ?? 75);
$questions = $_POST['questions'] ?? '';
$adminId = $_SESSION['admin_id'];

// Validate required fields
if (empty($title) || empty($description) || empty($section) || empty($questions)) {
    jsonResponse(['success' => false, 'error' => 'Missing required fields']);
}

// Validate questions JSON
$questionsData = json_decode($questions, true);
if (!$questionsData || count($questionsData) < 5) {
    jsonResponse(['success' => false, 'error' => 'Minimum 5 questions required']);
}

// Validate each question
foreach ($questionsData as $q) {
    if (empty($q['question']) || empty($q['optionA']) || empty($q['optionB']) || 
        empty($q['optionC']) || empty($q['optionD']) || empty($q['correctAnswer'])) {
        jsonResponse(['success' => false, 'error' => 'All question fields are required']);
    }
    if (!in_array($q['correctAnswer'], ['A', 'B', 'C', 'D'])) {
        jsonResponse(['success' => false, 'error' => 'Invalid correct answer option']);
    }
}

// Handle PDF file upload
$filename = null;
if (isset($_FILES['pdfFile']) && $_FILES['pdfFile']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = __DIR__ . '/../public/modules/';
    
    // Create directory if it doesn't exist
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // Check file type
    $fileType = $_FILES['pdfFile']['type'];
    if ($fileType !== 'application/pdf') {
        jsonResponse(['success' => false, 'error' => 'Only PDF files are allowed']);
    }
    
    // Check file size (10MB limit)
    if ($_FILES['pdfFile']['size'] > 10 * 1024 * 1024) {
        jsonResponse(['success' => false, 'error' => 'File size must be less than 10MB']);
    }
    
    $originalName = $_FILES['pdfFile']['name'];
    $uniqueName = time() . '-' . $originalName;
    $uploadPath = $uploadDir . $uniqueName;
    
    if (move_uploaded_file($_FILES['pdfFile']['tmp_name'], $uploadPath)) {
        $filename = $uniqueName;
    } else {
        jsonResponse(['success' => false, 'error' => 'File upload failed']);
    }
}

if (!$filename) {
    jsonResponse(['success' => false, 'error' => 'PDF file is required']);
}

try {
    // Check if modules table has new columns, if not add them
    $stmt = $pdo->query("SHOW COLUMNS FROM modules LIKE 'title'");
    if ($stmt->rowCount() == 0) {
        // Add new columns to support enhanced module structure
        $pdo->exec("ALTER TABLE modules 
                   ADD COLUMN title VARCHAR(255) AFTER id,
                   ADD COLUMN description TEXT AFTER title,
                   ADD COLUMN section VARCHAR(100) AFTER description,
                   ADD COLUMN passing_score INT DEFAULT 75 AFTER section");
    }
    
    $stmt = $pdo->prepare("INSERT INTO modules (title, description, section, passing_score, quarter, filename, questions, admin_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$title, $description, $section, $passingScore, 'Q1', $filename, $questions, $adminId]);
    
    jsonResponse(['success' => true, 'message' => 'Module created successfully']);
    
} catch (PDOException $e) {
    // If upload was successful but DB failed, clean up the file
    if ($filename && file_exists($uploadDir . $filename)) {
        unlink($uploadDir . $filename);
    }
    jsonResponse(['success' => false, 'error' => 'Database error: ' . $e->getMessage()], 500);
}
?>
