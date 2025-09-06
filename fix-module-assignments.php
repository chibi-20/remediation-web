<?php
require_once 'config.php';

try {
    $db = new Database();
    $pdo = $db->getConnection();
    
    echo "=== CURRENT MODULE ASSIGNMENTS ===\n";
    $stmt = $pdo->query('SELECT id, title, section, teacher_id FROM modules');
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "Module " . $row['id'] . ": " . $row['title'] . " | Section: " . $row['section'] . " | Teacher: " . $row['teacher_id'] . "\n";
    }
    
    echo "\n=== FIXING MODULE ASSIGNMENTS ===\n";
    
    // Module 13 "AP WEEK 2" covers "LEGASPI, 10-LEYNES" 
    // Since this is an AP subject and JAY MAR teaches ARALING PANLIPUNAN, let's assign it to him
    $stmt = $pdo->prepare('UPDATE modules SET teacher_id = 1 WHERE title LIKE "%AP%" OR description LIKE "%AP%"');
    $stmt->execute();
    $affected = $stmt->rowCount();
    echo "✅ Assigned AP modules to JAY MAR (teacher_id = 1): $affected modules\n";
    
    // Keep ENGLISH modules with ROSEMARIE since she teaches ENGLISH
    echo "✅ ENGLISH modules remain with ROSEMARIE (teacher_id = 2)\n";
    
    echo "\n=== UPDATED MODULE ASSIGNMENTS ===\n";
    $stmt = $pdo->query('SELECT id, title, section, teacher_id FROM modules');
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "Module " . $row['id'] . ": " . $row['title'] . " | Section: " . $row['section'] . " | Teacher: " . $row['teacher_id'] . "\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
