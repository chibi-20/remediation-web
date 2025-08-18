<?php
// api/get-module-assessment.php - Get module assessment questions
require_once '../config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(['success' => false, 'error' => 'Method not allowed'], 405);
}

$moduleId = intval($_GET['id'] ?? 0);
$studentLRN = sanitizeInput($_GET['studentLRN'] ?? '');

if (!$moduleId || !$studentLRN) {
    jsonResponse(['success' => false, 'error' => 'Missing required parameters']);
}

try {
    // First, find the student and get their assigned teacher
    $stmt = $pdo->prepare("SELECT id, admin_id FROM students WHERE lrn = ?");
    $stmt->execute([$studentLRN]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$student) {
        jsonResponse(['success' => false, 'error' => 'Student not found']);
    }
    
    // Get module details AND verify it belongs to the student's teacher
    $stmt = $pdo->prepare("SELECT id, title, description, questions, passing_score, filename FROM modules WHERE id = ? AND admin_id = ?");
    $stmt->execute([$moduleId, $student['admin_id']]);
    $module = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$module) {
        jsonResponse(['success' => false, 'error' => 'Module not found or access denied']);
    }
    
    // Check if student has already taken the assessment
    $stmt = $pdo->prepare("SELECT score, passed, taken_at FROM assessments WHERE student_id = ? AND module_id = ?");
    $stmt->execute([$student['id'], $moduleId]);
    $assessment = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $questions = json_decode($module['questions'], true);
    
    // Remove correct answers from questions (for security)
    $questionsForStudent = [];
    foreach ($questions as $q) {
        $questionsForStudent[] = [
            'question' => $q['question'],
            'optionA' => $q['optionA'],
            'optionB' => $q['optionB'],
            'optionC' => $q['optionC'],
            'optionD' => $q['optionD']
        ];
    }
    
    jsonResponse([
        'success' => true,
        'module' => [
            'id' => $module['id'],
            'title' => $module['title'],
            'description' => $module['description'],
            'filename' => $module['filename'],
            'passingScore' => $module['passing_score'],
            'questions' => $questionsForStudent,
            'totalQuestions' => count($questionsForStudent)
        ],
        'previousAttempt' => $assessment
    ]);
    
} catch (PDOException $e) {
    jsonResponse(['success' => false, 'error' => 'Database error'], 500);
}
?>
    
    $questions = json_decode($module['questions'], true);
    
    // Remove correct answers from questions (for security)
    $questionsForStudent = [];
    foreach ($questions as $q) {
        $questionsForStudent[] = [
            'question' => $q['question'],
            'optionA' => $q['optionA'],
            'optionB' => $q['optionB'],
            'optionC' => $q['optionC'],
            'optionD' => $q['optionD']
        ];
    }
    
    jsonResponse([
        'success' => true,
        'module' => [
            'id' => $module['id'],
            'title' => $module['title'],
            'description' => $module['description'],
            'filename' => $module['filename'],
            'passingScore' => $module['passing_score'],
            'questions' => $questionsForStudent,
            'totalQuestions' => count($questionsForStudent)
        ],
        'previousAttempt' => $assessment
    ]);
    
} catch (PDOException $e) {
    jsonResponse(['success' => false, 'error' => 'Database error'], 500);
}
?>
