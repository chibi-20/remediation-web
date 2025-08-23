<?php
// api/teacher-create-module.php - Create module endpoint for teachers
require_once '../config.php';
require_once '../secure-upload.php';
require_once '../security-middleware.php';

// Apply upload security checks
SecurityMiddleware::checkUploadSecurity();

header('Content-Type: application/json');

// Check teacher authentication
session_start();
if (!isset($_SESSION['teacher_logged_in']) || !$_SESSION['teacher_logged_in'] || !isset($_SESSION['teacher_id'])) {
    jsonResponse(false, 'Teacher authentication required', null, 401);
}

$teacherId = $_SESSION['teacher_id'];

$title = sanitizeInput($_POST['title'] ?? '');
$description = sanitizeInput($_POST['description'] ?? '');
$sectionsJson = $_POST['sections'] ?? '';
$passingScore = intval($_POST['passingScore'] ?? 75);
$questions = $_POST['questions'] ?? '';

// Parse sections array
$sections = json_decode($sectionsJson, true);
if (!$sections || !is_array($sections) || empty($sections)) {
    jsonResponse(false, 'At least one section must be selected');
}

// Validate required fields
if (empty($title) || empty($description) || empty($questions)) {
    jsonResponse(false, 'Missing required fields');
}

// Validate questions JSON
$questionsData = json_decode($questions, true);
if (!$questionsData || count($questionsData) < 5) {
    jsonResponse(false, 'Minimum 5 questions required');
}

// Validate each question
foreach ($questionsData as $q) {
    if (empty($q['question']) || empty($q['optionA']) || empty($q['optionB']) || 
        empty($q['optionC']) || empty($q['optionD']) || empty($q['correctAnswer'])) {
        jsonResponse(false, 'All question fields are required');
    }
    if (!in_array($q['correctAnswer'], ['A', 'B', 'C', 'D'])) {
        jsonResponse(false, 'Invalid correct answer option');
    }
}

// Handle secure PDF file upload
$filename = null;
try {
    $filename = SecureFileUpload::handleUpload('pdfFile', ['pdf']);
} catch (Exception $e) {
    jsonResponse(false, 'File upload error: ' . $e->getMessage());
}

if (!$filename) {
    jsonResponse(false, 'PDF file is required');
}

try {
    $db = new Database();
    $pdo = $db->getConnection();
    
    // Get teacher's information to find their admin_id
    $stmt = $pdo->prepare("SELECT username FROM teachers WHERE id = ?");
    $stmt->execute([$teacherId]);
    $teacher = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$teacher) {
        jsonResponse(false, 'Teacher not found');
    }
    
    // Find the corresponding admin_id for this teacher
    $stmt = $pdo->prepare("SELECT id FROM admins WHERE username = ?");
    $stmt->execute([$teacher['username']]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$admin) {
        jsonResponse(false, 'Teacher admin record not found');
    }
    
    $adminId = $admin['id'];
    
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
    
    // Start transaction to ensure all sections are created or none
    $pdo->beginTransaction();
    
    $stmt = $pdo->prepare("INSERT INTO modules (title, description, section, passing_score, quarter, filename, questions, admin_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    
    // Create module for each selected section
    $createdModules = [];
    foreach ($sections as $section) {
        $section = sanitizeInput($section);
        $stmt->execute([$title, $description, $section, $passingScore, 'Q1', $filename, $questions, $adminId]);
        $createdModules[] = [
            'id' => $pdo->lastInsertId(),
            'section' => $section
        ];
    }
    
    $pdo->commit();
    
    $sectionsText = implode(', ', $sections);
    jsonResponse(true, "Module created successfully for sections: {$sectionsText}", $createdModules);
    
} catch (PDOException $e) {
    $pdo->rollBack();
    // If upload was successful but DB failed, clean up the file
    if ($filename && file_exists($uploadDir . $filename)) {
        unlink($uploadDir . $filename);
    }
    error_log("Error creating teacher module: " . $e->getMessage());
    jsonResponse(false, 'Database error: ' . $e->getMessage(), null, 500);
}
?>
