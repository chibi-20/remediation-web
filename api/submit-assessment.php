<?php
// api/submit-assessment.php - Submit student assessment answers
require_once '../config.php';
require_once '../security-middleware.php';

// Apply API security checks
SecurityMiddleware::checkAPISecurity();

header('Content-Type: application/json');

$rawInput = file_get_contents('php://input');
$input = json_decode($rawInput, true);

error_log("Decoded input: " . json_encode($input));

$moduleId = intval($input['moduleId'] ?? 0);
$studentLRN = sanitizeInput($input['studentLRN'] ?? '');
$answers = $input['answers'] ?? [];

error_log("Assessment submission - ModuleID: $moduleId, StudentLRN: $studentLRN, Answers: " . json_encode($answers));

if (!$moduleId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Module ID is required']);
    exit;
}

if (!$studentLRN) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Student LRN is required']);
    exit;
}

if (empty($answers)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Answers are required']);
    exit;
}

try {
    $db = new Database();
    $pdo = $db->getConnection();
    
    // Get student information
    $stmt = $pdo->prepare("SELECT * FROM students WHERE lrn = ?");
    $stmt->execute([$studentLRN]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$student) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Student not found']);
        exit;
    }
    
    // Get module details and questions
    $stmt = $pdo->prepare("SELECT * FROM modules WHERE id = ?");
    $stmt->execute([$moduleId]);
    $module = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$module) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Module not found']);
        exit;
    }
    
    $questions = json_decode($module['questions'], true);
    if (!$questions) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Module has no questions']);
        exit;
    }
    
    // Calculate score
    $totalQuestions = count($questions);
    $correctAnswers = 0;
    
    foreach ($answers as $questionIndex => $studentAnswer) {
        if (isset($questions[$questionIndex]) && 
            $questions[$questionIndex]['correctAnswer'] === $studentAnswer) {
            $correctAnswers++;
        }
    }
    
    $score = $correctAnswers;
    $percentage = round(($correctAnswers / $totalQuestions) * 100);
    $passed = $percentage >= ($module['passing_score'] ?? 75);
    
    // Update student progress
    $currentProgress = $student['progress'] ? json_decode($student['progress'], true) : [];
    $moduleKey = "module{$moduleId}";
    
    $currentProgress[$moduleKey] = [
        'score' => $score,
        'total' => $totalQuestions,
        'percentage' => $percentage,
        'passed' => $passed,
        'completed_at' => date('Y-m-d H:i:s'),
        'answers' => $answers
    ];
    
    // Save updated progress
    $stmt = $pdo->prepare("UPDATE students SET progress = ? WHERE id = ?");
    $stmt->execute([json_encode($currentProgress), $student['id']]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Assessment submitted successfully',
        'data' => [
            'score' => $score,
            'total' => $totalQuestions,
            'percentage' => $percentage,
            'passed' => $passed,
            'passing_score' => $module['passing_score'] ?? 75
        ]
    ]);
    
} catch (PDOException $e) {
    error_log("Error submitting assessment: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>
