<?php
// api/register-student.php - Student registration endpoint
require_once '../config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$firstName = sanitizeInput($input['firstName'] ?? '');
$lastName = sanitizeInput($input['lastName'] ?? '');
$middleInitial = sanitizeInput($input['middleInitial'] ?? '');
$grade = sanitizeInput($input['grade'] ?? '');
$section = sanitizeInput($input['section'] ?? '');
$lrn = sanitizeInput($input['lrn'] ?? '');
$adminId = $input['adminId'] ?? null;
$password = $input['password'] ?? '';

// Validate password
if (strlen($password) < 6) {
    echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters.']);
    exit;
}

$passwordHash = password_hash($password, PASSWORD_DEFAULT);

// Validate required fields
$error = validateRequired(['firstName', 'lastName', 'grade', 'lrn'], $input);
if ($error) {
    echo json_encode(['success' => false, 'message' => $error]);
    exit;
}

// Validate LRN format (should be 12 digits)
if (!preg_match('/^\d{12}$/', $lrn)) {
    echo json_encode(['success' => false, 'message' => 'LRN must be exactly 12 digits.']);
    exit;
}

try {
    $db = new Database();
    $pdo = $db->getConnection();
    
    // Check if LRN already exists
    $stmt = $pdo->prepare("SELECT * FROM students WHERE lrn = ?");
    $stmt->execute([$lrn]);
    
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'LRN already exists.']);
        exit;
    }
    
    // Insert new student with teacher assignment
    $stmt = $pdo->prepare("INSERT INTO students (firstName, lastName, grade, section, lrn, password, teacher_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$firstName, $lastName, $grade, $section, $lrn, $passwordHash, $adminId]);
    
    $studentId = $pdo->lastInsertId();
    
    echo json_encode([
        'success' => true, 
        'message' => 'Student registered successfully',
        'data' => ['id' => $studentId]
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Database error: ' . $e->getMessage(),
        'code' => $e->getCode(),
        'debug' => [
            'firstName' => $firstName,
            'lastName' => $lastName, 
            'grade' => $grade,
            'section' => $section,
            'lrn' => $lrn,
            'adminId' => $adminId
        ]
    ]);
}
?>
