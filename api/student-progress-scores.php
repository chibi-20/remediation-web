<?php
// api/student-progress-scores.php - Get student scores for teacher dashboard
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config.php';

try {
    $teacher_id = $_GET['teacher_id'] ?? null;
    
    if (!$teacher_id) {
        echo json_encode(['success' => false, 'error' => 'Teacher ID required']);
        exit;
    }

    $db = new Database();
    $pdo = $db->getConnection();

    // Get teacher information to check what sections they teach
    $teacherSQL = "SELECT advisory_section, sections FROM teachers WHERE id = :teacher_id";
    $teacherStmt = $pdo->prepare($teacherSQL);
    $teacherStmt->bindParam(':teacher_id', $teacher_id, PDO::PARAM_INT);
    $teacherStmt->execute();
    $teacher = $teacherStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$teacher) {
        echo json_encode([
            'success' => false,
            'error' => 'Teacher not found: ' . $teacher_id,
            'debug' => ['teacher_id' => $teacher_id]
        ]);
        exit;
    }

    // Get advisory students (students directly assigned to this teacher)
    $advisoryStudentsSQL = "SELECT id, firstName, lastName, lrn, section, progress FROM students WHERE teacher_id = :teacher_id";
    $advisoryStmt = $pdo->prepare($advisoryStudentsSQL);
    $advisoryStmt->bindParam(':teacher_id', $teacher_id, PDO::PARAM_INT);
    $advisoryStmt->execute();
    $advisoryStudents = $advisoryStmt->fetchAll(PDO::FETCH_ASSOC);

    // Get students from sections this teacher teaches (subject teaching)
    $teachingSections = [];
    if (!empty($teacher['sections'])) {
        $sectionsArray = array_map('trim', explode(',', $teacher['sections']));
        foreach ($sectionsArray as $section) {
            // Clean section names (remove grade prefixes like "10-")
            $cleanSection = preg_replace('/^\d+-/', '', $section);
            $teachingSections[] = $cleanSection;
        }
    }
    
    $subjectStudents = [];
    if (!empty($teachingSections)) {
        $placeholders = str_repeat('?,', count($teachingSections) - 1) . '?';
        $subjectStudentsSQL = "SELECT id, firstName, lastName, lrn, section, progress FROM students WHERE section IN ($placeholders)";
        $subjectStmt = $pdo->prepare($subjectStudentsSQL);
        $subjectStmt->execute($teachingSections);
        $subjectStudents = $subjectStmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Combine and deduplicate students
    $allStudents = array_merge($advisoryStudents, $subjectStudents);
    $students = [];
    $seenIds = [];
    
    foreach ($allStudents as $student) {
        if (!in_array($student['id'], $seenIds)) {
            $students[] = $student;
            $seenIds[] = $student['id'];
        }
    }
    
    if (empty($students)) {
        echo json_encode([
            'success' => false,
            'error' => 'No students found for teacher ID: ' . $teacher_id,
            'debug' => [
                'teacher_id' => $teacher_id, 
                'advisory_section' => $teacher['advisory_section'],
                'teaching_sections' => $teacher['sections'],
                'cleaned_sections' => $teachingSections,
                'advisory_students_count' => count($advisoryStudents),
                'subject_students_count' => count($subjectStudents)
            ]
        ]);
        exit;
    }

    // Get modules using teacher_id with section information
    $modulesSQL = "SELECT id, title, description, section, quarter, filename, passing_score FROM modules WHERE teacher_id = :teacher_id";
    $modulesStmt = $pdo->prepare($modulesSQL);
    $modulesStmt->bindParam(':teacher_id', $teacher_id, PDO::PARAM_INT);
    $modulesStmt->execute();
    $modules = $modulesStmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($modules)) {
        echo json_encode([
            'success' => false,
            'error' => 'No modules found for teacher ID: ' . $teacher_id,
            'debug' => [
                'teacher_id' => $teacher_id, 
                'students_count' => count($students),
                'modules_count' => 0,
                'message' => 'Modules should use teacher_id, not admin_id'
            ]
        ]);
        exit;
    }

    // Get all assessments for students and modules (from students.progress JSON)
    $studentProgressData = [];
    foreach ($students as $student) {
        if (!empty($student['progress'])) {
            $progressJson = json_decode($student['progress'], true);
            if ($progressJson) {
                $studentProgressData[$student['id']] = $progressJson;
            }
        }
    }

    // Return students and modules data with actual assessment results
    $results = [];
    foreach ($students as $student) {
        foreach ($modules as $module) {
            // Check if student's section matches module's sections
            $modulesSections = array_map('trim', explode(',', $module['section']));
            $studentSection = trim($student['section']);
            
            // Check if student's section is in module's sections (handle "10-LEYNES" format)
            $sectionMatch = false;
            foreach ($modulesSections as $moduleSection) {
                // Clean module section (remove grade prefixes like "10-")
                $cleanModuleSection = preg_replace('/^\d+-/', '', $moduleSection);
                
                if (strpos($moduleSection, $studentSection) !== false || 
                    strpos($studentSection, $moduleSection) !== false ||
                    $moduleSection === $studentSection ||
                    $cleanModuleSection === $studentSection) {
                    $sectionMatch = true;
                    break;
                }
            }
            
            if ($sectionMatch) {
                // Get assessment data from student progress JSON
                $progressData = $studentProgressData[$student['id']] ?? [];
                $moduleKey = 'module' . $module['id'];
                $moduleProgress = $progressData[$moduleKey] ?? null;
                
                $results[] = [
                    'student_id' => $student['id'],
                    'firstName' => $student['firstName'],
                    'lastName' => $student['lastName'],
                    'lrn' => $student['lrn'],
                    'section' => $student['section'],
                    'module_id' => $module['id'],
                    'title' => $module['title'],
                    'subject' => $module['title'], // Use title as subject
                    'lesson' => $module['description'] ?: 'No Description',
                    'quarter' => $module['quarter'],
                    'filename' => $module['filename'],
                    'passing_score' => $module['passing_score'],
                    'completed' => $moduleProgress ? 1 : 0,
                    'score' => $moduleProgress ? $moduleProgress['score'] : null,
                    'total' => $moduleProgress ? $moduleProgress['total'] : null,
                    'percentage' => $moduleProgress ? $moduleProgress['percentage'] : null,
                    'passed' => $moduleProgress ? $moduleProgress['passed'] : null,
                    'completed_at' => $moduleProgress ? $moduleProgress['completed_at'] : null
                ];
            }
        }
    }

    echo json_encode([
        'success' => true,
        'data' => $results,
        'total_records' => count($results),
        'debug' => [
            'teacher_id' => $teacher_id,
            'teacher_advisory_section' => $teacher['advisory_section'],
            'teacher_teaching_sections' => $teacher['sections'],
            'cleaned_teaching_sections' => $teachingSections,
            'students_count' => count($students),
            'modules_count' => count($modules),
            'progress_data_count' => count($studentProgressData),
            'results_count' => count($results),
            'message' => 'Data loaded with cross-section access for subject teachers'
        ]
    ]);

} catch (Exception $e) {
    error_log("Student progress scores error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Failed to load student progress scores: ' . $e->getMessage()
    ]);
}
?>
