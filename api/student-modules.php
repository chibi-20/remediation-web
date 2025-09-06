<?php
// api/student-modules.php - Get modules for student based on section teachers
require_once '../config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$lrn = $_GET['lrn'] ?? '';

if (!$lrn) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'LRN is required']);
    exit;
}

try {
    $db = new Database();
    $pdo = $db->getConnection();
    
    // Get student information
    $stmt = $pdo->prepare("SELECT * FROM students WHERE lrn = ?");
    $stmt->execute([$lrn]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$student) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Student not found']);
        exit;
    }
    
    // Get modules for this student through multiple approaches:
    // 1. From teachers assigned to student's section via teacher_sections
    // 2. From student's direct teacher assignment
    // 3. From any modules created specifically for student's section
    
    $allTeacherIds = [];
    
    // Approach 1: Get teachers assigned to this student's section via teacher_sections
    $stmt = $pdo->prepare("
        SELECT DISTINCT ts.teacher_id 
        FROM teacher_sections ts 
        WHERE ts.section = ?
    ");
    $stmt->execute([$student['section']]);
    $sectionTeacherIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $allTeacherIds = array_merge($allTeacherIds, $sectionTeacherIds);
    
    // Approach 2: Include the student's direct teacher_id
    if ($student['teacher_id']) {
        $allTeacherIds[] = $student['teacher_id'];
    }
    
    // Approach 3: Get all teachers who have created modules for this section (direct section match)
    $stmt = $pdo->prepare("
        SELECT DISTINCT teacher_id FROM modules 
        WHERE section = ? OR section = ? OR section LIKE ? OR section LIKE ?
    ");
    $sectionVariations = [
        $student['section'],                    // exact match: "LEYNES"
        $student['grade'] . '-' . $student['section'],  // with grade: "10-LEYNES"  
        '%' . $student['section'] . '%',       // contains: "%LEYNES%"
        '%' . $student['section']              // ends with: "%LEYNES"
    ];
    $stmt->execute($sectionVariations);
    $moduleTeacherIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $allTeacherIds = array_merge($allTeacherIds, $moduleTeacherIds);
    
    // Remove duplicates
    $teacherIds = array_unique($allTeacherIds);
    
    if (empty($teacherIds)) {
        echo json_encode([
            'success' => true, 
            'message' => 'No modules found - no teachers assigned',
            'data' => [
                'modules' => [], 
                'student' => $student,
                'student_teacher_id' => $student['teacher_id'],
                'section_variations_tried' => $sectionVariations
            ]
        ]);
        exit;
    }
    
    // Get modules from ALL teachers assigned to this section (flexible section matching)
    $placeholders = str_repeat('?,', count($teacherIds) - 1) . '?';
    $stmt = $pdo->prepare("
        SELECT m.*, t.name as teacher_name 
        FROM modules m 
        JOIN teachers t ON m.teacher_id = t.id 
        WHERE m.teacher_id IN ($placeholders) 
        AND (m.section = ? OR m.section = ? OR m.section LIKE ? OR m.section LIKE ?)
        ORDER BY m.quarter, m.id
    ");
    
    // Prepare section variations to match
    $sectionVariations = [
        $student['section'],                    // exact match: "LEYNES"
        $student['grade'] . '-' . $student['section'],  // with grade: "10-LEYNES"  
        '%' . $student['section'] . '%',       // contains: "%LEYNES%"
        '%' . $student['section']              // ends with: "%LEYNES"
    ];
    
    $executeParams = array_merge($teacherIds, $sectionVariations);
    $stmt->execute($executeParams);
    $modules = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Parse questions JSON for each module
    foreach ($modules as &$module) {
        $module['questions'] = $module['questions'] ? json_decode($module['questions'], true) : [];
    }
    
    // Parse student progress
    $student['progress'] = $student['progress'] ? json_decode($student['progress'], true) : [];
    
    // Get adviser/teacher information
    $adviser = null;
    if ($student['teacher_id']) {
        $stmt = $pdo->prepare("SELECT name, username, subject FROM teachers WHERE id = ?");
        $stmt->execute([$student['teacher_id']]);
        $adviser = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Data loaded successfully',
        'data' => [
            'modules' => $modules,
            'student' => $student,
            'adviser' => $adviser
        ]
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>
