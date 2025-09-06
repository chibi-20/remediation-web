<?php
require_once 'config.php';

try {
    $db = new Database();
    $pdo = $db->getConnection();
    
    // Update LEYNES student to belong to teacher_id 2
    $stmt = $pdo->prepare('UPDATE students SET teacher_id = 2 WHERE section = "LEYNES"');
    $result = $stmt->execute();
    
    echo "Updated LEYNES student to teacher_id = 2\n";
    echo "Affected rows: " . $stmt->rowCount() . "\n";
    
    // Verify the update
    echo "\n=== UPDATED STUDENTS FOR TEACHER_ID = 2 ===\n";
    $stmt = $pdo->query('SELECT id, firstName, lastName, section, teacher_id FROM students WHERE teacher_id = 2');
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        print_r($row);
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
