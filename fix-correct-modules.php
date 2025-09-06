<?php
require_once 'config.php';

try {
    $db = new Database();
    $pdo = $db->getConnection();
    
    echo "=== CORRECT TEACHER-SUBJECT-SECTION MAPPING ===\n";
    echo "JAY MAR (ARALING PANLIPUNAN teacher):\n";
    echo "- Advisory: LEYNES\n";
    echo "- Teaches AP to: LEGASPI (as subject teacher)\n";
    echo "\nROSEMARIE (ENGLISH teacher):\n";
    echo "- Advisory: LEGASPI\n";
    echo "- Teaches ENGLISH to: LEYNES (as subject teacher)\n";
    
    echo "\n=== FIXING THE MODULE ASSIGNMENT ERROR ===\n";
    
    // Delete the incorrectly created AP module for ROSEMARIE
    $stmt = $pdo->prepare('DELETE FROM modules WHERE id = 14');
    $stmt->execute();
    echo "✅ Deleted incorrect AP module assigned to ROSEMARIE\n";
    
    // The correct approach: Create ENGLISH modules for LEYNES (taught by ROSEMARIE)
    // and keep AP modules with JAY MAR
    
    // Create ENGLISH module for LEYNES (ROSEMARIE teaches ENGLISH to LEYNES)
    $stmt = $pdo->prepare('INSERT INTO modules (title, description, section, grade, passing_score, quarter, filename, questions, teacher_id, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt->execute([
        'ENGLISH (LEYNES)', 
        'WEEK 2',
        'LEYNES', 
        '10',
        75,
        'Q1',
        '1757128830-59f034d64a2ce760-Esports_Guidelines_Intramurals.pdf', // Same file as Module 9
        '[{"question":"A","optionA":"A","optionB":"B","optionC":"C","optionD":"D","correctAnswer":"A"}]', // Sample questions
        2, // ROSEMARIE's teacher_id
        date('Y-m-d H:i:s')
    ]);
    
    $newModuleId = $pdo->lastInsertId();
    echo "✅ Created Module $newModuleId: ENGLISH (LEYNES) for ROSEMARIE\n";
    
    // Restore Module 13 to cover both sections since JAY MAR can teach AP to both
    $stmt = $pdo->prepare('UPDATE modules SET section = "LEGASPI, LEYNES" WHERE id = 13');
    $stmt->execute();
    echo "✅ Updated Module 13 to cover both sections (JAY MAR teaches AP to both)\n";
    
    echo "\n=== FINAL CORRECT MODULE STRUCTURE ===\n";
    $stmt = $pdo->query('SELECT id, title, section, teacher_id FROM modules ORDER BY id');
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $teacherName = $row['teacher_id'] == 1 ? 'JAY MAR (AP)' : 'ROSEMARIE (ENGLISH)';
        echo "Module " . $row['id'] . ": " . $row['title'] . " | Section: " . $row['section'] . " | Teacher: " . $teacherName . "\n";
    }
    
    echo "\n=== EXPECTED DASHBOARD RESULTS ===\n";
    echo "CHIBI (LEYNES) should see:\n";
    echo "- ENGLISH (LEYNES) - created by ROSEMARIE ✅\n";
    echo "- AP WEEK 2 - created by JAY MAR ✅\n";
    echo "\nMA TERESA (LEGASPI) should see:\n";
    echo "- ENGLISH - created by ROSEMARIE ✅\n";
    echo "- AP - created by JAY MAR ✅\n";
    echo "- AP WEEK 2 - created by JAY MAR ✅\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
