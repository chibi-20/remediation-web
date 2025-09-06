<?php
require_once 'config.php';

try {
    $db = new Database();
    $pdo = $db->getConnection();
    
    echo "=== FIXING STUDENT ASSIGNMENTS ===\n";
    
    // CHIBI CANTURIA should belong to JAY MAR (teacher_id = 1) since he's the LEYNES adviser
    $stmt = $pdo->prepare('UPDATE students SET teacher_id = 1 WHERE section = "LEYNES"');
    $stmt->execute();
    echo "✅ Restored CHIBI CANTURIA (LEYNES) to JAY MAR CANTURIA (teacher_id = 1)\n";
    
    // MA TERESA LAJO should belong to ROSEMARIE (teacher_id = 2) since she's the LEGASPI adviser  
    $stmt = $pdo->prepare('UPDATE students SET teacher_id = 2 WHERE section = "LEGASPI"');
    $stmt->execute();
    echo "✅ Confirmed MA TERESA LAJO (LEGASPI) with ROSEMARIE CANTURIA (teacher_id = 2)\n";
    
    echo "\n=== UPDATED STUDENT ASSIGNMENTS ===\n";
    $stmt = $pdo->query('SELECT id, firstName, lastName, section, teacher_id FROM students');
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "Student: " . $row['firstName'] . " " . $row['lastName'] . " | Section: " . $row['section'] . " | Teacher ID: " . $row['teacher_id'] . "\n";
    }
    
    echo "\n=== TEACHER-STUDENT MAPPING ===\n";
    echo "JAY MAR (ID: 1) - Advisory: LEYNES, Teaching: 10-LEGASPI\n";
    echo "  └── CHIBI CANTURIA (LEYNES student)\n";
    echo "ROSEMARIE (ID: 2) - Advisory: LEGASPI, Teaching: 10-LEYNES\n"; 
    echo "  └── MA TERESA LAJO (LEGASPI student)\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
