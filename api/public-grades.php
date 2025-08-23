<?php
require_once '../config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

try {
    $db = new Database();
    $pdo = $db->getConnection();
    
    // Get unique grades from students table
    $stmt = $pdo->query("SELECT DISTINCT grade FROM students WHERE grade IS NOT NULL AND grade != '' ORDER BY grade ASC");
    $grades = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // If no grades in students table, get from teachers
    if (empty($grades)) {
        $stmt = $pdo->query("SELECT DISTINCT grade FROM teachers WHERE grade IS NOT NULL AND grade != '' ORDER BY grade ASC");
        $grades = $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    // If still no grades, provide default grades
    if (empty($grades)) {
        $grades = ['7', '8', '9', '10', '11', '12'];
    }
    
    $result = [];
    foreach ($grades as $grade) {
        $result[] = [
            'grade' => (string)$grade
        ];
    }
    
    echo json_encode($result);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
