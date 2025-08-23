<?php
require_once 'config.php';

header('Content-Type: application/json');

try {
    $db = new Database();
    $pdo = $db->getConnection();
    
    // Check the actual structure of the students table
    $stmt = $pdo->query("DESCRIBE students");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'message' => 'Students table structure',
        'data' => [
            'columns' => $columns,
            'total_columns' => count($columns)
        ]
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
