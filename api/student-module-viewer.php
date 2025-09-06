<?php
// api/student-module-viewer.php - Get module details for student viewing
require_once '../config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$moduleId = intval($_GET['id'] ?? 0);
$studentLRN = sanitizeInput($_GET['lrn'] ?? '');

if (!$moduleId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Module ID is required']);
    exit;
}

try {
    $db = new Database();
    $pdo = $db->getConnection();
    
    // Get student information if LRN provided
    $student = null;
    if ($studentLRN) {
        $stmt = $pdo->prepare("SELECT * FROM students WHERE lrn = ?");
        $stmt->execute([$studentLRN]);
        $student = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Get module details
    $stmt = $pdo->prepare("
        SELECT m.*, t.name as teacher_name 
        FROM modules m 
        JOIN teachers t ON m.teacher_id = t.id 
        WHERE m.id = ?
    ");
    $stmt->execute([$moduleId]);
    $module = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$module) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Module not found']);
        exit;
    }
    
    // Parse questions
    $module['questions'] = $module['questions'] ? json_decode($module['questions'], true) : [];
    
    // Remove correct answers for security (students shouldn't see them)
    $questionsForStudent = [];
    foreach ($module['questions'] as $q) {
        $questionsForStudent[] = [
            'question' => $q['question'],
            'optionA' => $q['optionA'],
            'optionB' => $q['optionB'],
            'optionC' => $q['optionC'],
            'optionD' => $q['optionD']
        ];
    }
    $module['questions'] = $questionsForStudent;
    
    // Get student's progress on this module if student is provided
    $studentProgress = null;
    if ($student) {
        $progress = $student['progress'] ? json_decode($student['progress'], true) : [];
        $moduleKey = "module{$moduleId}";
        $studentProgress = $progress[$moduleKey] ?? null;
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Module retrieved successfully',
        'data' => [
            'module' => $module,
            'student_progress' => $studentProgress,
            'student' => $student
        ]
    ]);
    
} catch (PDOException $e) {
    error_log("Error fetching module: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>
