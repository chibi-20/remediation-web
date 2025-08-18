<?php
// api/take-assessment.php - Handle student assessment submissions
require_once '../config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'error' => 'Method not allowed'], 405);
}

$input = json_decode(file_get_contents('php://input'), true);
$moduleId = intval($input['moduleId'] ?? 0);
$studentLRN = sanitizeInput($input['studentLRN'] ?? '');
$answers = $input['answers'] ?? [];

// Validate input
if (!$moduleId || !$studentLRN || empty($answers)) {
    jsonResponse(['success' => false, 'error' => 'Missing required fields']);
}

try {
    // First, find the student and get their assigned teacher
    $stmt = $pdo->prepare("SELECT id, admin_id FROM students WHERE lrn = ?");
    $stmt->execute([$studentLRN]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$student) {
        jsonResponse(['success' => false, 'error' => 'Student not found']);
    }
    
    // Get module details and questions, but only if it belongs to the student's teacher
    $stmt = $pdo->prepare("SELECT id, title, questions, passing_score FROM modules WHERE id = ? AND admin_id = ?");
    $stmt->execute([$moduleId, $student['admin_id']]);
    $module = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$module) {
        jsonResponse(['success' => false, 'error' => 'Module not found or access denied']);
    }
    
    $questions = json_decode($module['questions'], true);
    if (!$questions) {
        jsonResponse(['success' => false, 'error' => 'Invalid module questions']);
    }
    
    // Calculate score
    $totalQuestions = count($questions);
    $correctAnswers = 0;
    
    for ($i = 0; $i < $totalQuestions; $i++) {
        if (isset($answers[$i]) && isset($questions[$i]['correctAnswer'])) {
            if ($answers[$i] === $questions[$i]['correctAnswer']) {
                $correctAnswers++;
            }
        }
    }
    
    $score = round(($correctAnswers / $totalQuestions) * 100, 2);
    $passed = $score >= $module['passing_score'];
    
    // Create assessments table if it doesn't exist
    $pdo->exec("CREATE TABLE IF NOT EXISTS assessments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        student_id INT,
        module_id INT,
        score DECIMAL(5,2),
        passed BOOLEAN,
        answers JSON,
        taken_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
        FOREIGN KEY (module_id) REFERENCES modules(id) ON DELETE CASCADE,
        UNIQUE KEY unique_student_module (student_id, module_id)
    )");
    
    // Save assessment result (replace if exists)
    $stmt = $pdo->prepare("REPLACE INTO assessments (student_id, module_id, score, passed, answers) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$student['id'], $moduleId, $score, $passed, json_encode($answers)]);
    
    // Update student progress if passed
    if ($passed) {
        $stmt = $pdo->prepare("SELECT progress FROM students WHERE id = ?");
        $stmt->execute([$student['id']]);
        $progressData = $stmt->fetchColumn();
        
        $progress = $progressData ? json_decode($progressData, true) : [];
        if (!isset($progress['modules'])) {
            $progress['modules'] = [];
        }
        
        // Mark module as completed
        $progress['modules'][$moduleId] = [
            'completed' => true,
            'score' => $score,
            'completed_at' => date('Y-m-d H:i:s')
        ];
        
        $stmt = $pdo->prepare("UPDATE students SET progress = ? WHERE id = ?");
        $stmt->execute([json_encode($progress), $student['id']]);
    }
    
    jsonResponse([
        'success' => true,
        'score' => $score,
        'passed' => $passed,
        'correctAnswers' => $correctAnswers,
        'totalQuestions' => $totalQuestions,
        'passingScore' => $module['passing_score'],
        'message' => $passed ? 'Congratulations! You passed the assessment.' : 'You need to score at least ' . $module['passing_score'] . '% to pass.'
    ]);
    
} catch (PDOException $e) {
    jsonResponse(['success' => false, 'error' => 'Database error'], 500);
}
?>
