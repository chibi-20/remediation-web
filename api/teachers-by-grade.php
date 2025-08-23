<?php
// api/teachers-by-grade.php - Get teachers filtered by grade level
require_once '../config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(false, 'Method not allowed', null, 405);
}

$grade = $_GET['grade'] ?? '';

if (!$grade) {
    jsonResponse(false, 'Grade parameter is required');
}

try {
    $db = new Database();
    $pdo = $db->getConnection();
    
    // Get teachers for the specified grade level
    $stmt = $pdo->prepare("
        SELECT id, name, username, advisory_section, subject, grade 
        FROM teachers 
        WHERE grade = ? 
        ORDER BY name ASC
    ");
    $stmt->execute([$grade]);
    $teachers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    jsonResponse(true, 'Teachers retrieved successfully', $teachers);
    
} catch (Exception $e) {
    error_log("Error fetching teachers by grade: " . $e->getMessage());
    jsonResponse(false, 'Error retrieving teachers: ' . $e->getMessage());
}
?>
