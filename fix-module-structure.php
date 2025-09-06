<?php
require_once 'config.php';

try {
    $db = new Database();
    $pdo = $db->getConnection();
    
    echo "=== FIXING MODULE ASSIGNMENTS FOR PROPER CROSS-SECTION ACCESS ===\n";
    
    // Current issue: Module 13 "AP WEEK 2" covers "LEGASPI, 10-LEYNES" but is assigned to JAY MAR
    // Solution: Create separate modules or assign based on which teacher teaches which section
    
    echo "\n=== CURRENT MODULES ===\n";
    $stmt = $pdo->query('SELECT * FROM modules');
    $modules = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($modules as $module) {
        echo "Module " . $module['id'] . ": " . $module['title'] . " | Section: " . $module['section'] . " | Teacher: " . $module['teacher_id'] . "\n";
    }
    
    echo "\n=== CREATING PROPER MODULE STRUCTURE ===\n";
    
    // For LEYNES students, they should see ROSEMARIE's modules since she teaches LEYNES
    // Let's create a duplicate of Module 13 for ROSEMARIE to teach LEYNES
    
    // First, get Module 13 details
    $stmt = $pdo->prepare('SELECT * FROM modules WHERE id = 13');
    $stmt->execute();
    $module13 = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($module13) {
        // Create ROSEMARIE's version for LEYNES section
        $stmt = $pdo->prepare('INSERT INTO modules (title, description, section, grade, passing_score, quarter, filename, questions, teacher_id, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            $module13['title'] . ' (LEYNES)', // Different title to distinguish
            $module13['description'],
            'LEYNES', // Only LEYNES section
            $module13['grade'],
            $module13['passing_score'],
            $module13['quarter'],
            $module13['filename'],
            $module13['questions'],
            2, // ROSEMARIE's teacher_id
            date('Y-m-d H:i:s')
        ]);
        
        $newModuleId = $pdo->lastInsertId();
        echo "✅ Created Module $newModuleId: " . $module13['title'] . " (LEYNES) for ROSEMARIE\n";
        
        // Update original Module 13 to only cover LEGASPI (for JAY MAR)
        $stmt = $pdo->prepare('UPDATE modules SET section = "LEGASPI" WHERE id = 13');
        $stmt->execute();
        echo "✅ Updated Module 13 to only cover LEGASPI (for JAY MAR)\n";
    }
    
    echo "\n=== UPDATED MODULE STRUCTURE ===\n";
    $stmt = $pdo->query('SELECT id, title, section, teacher_id FROM modules ORDER BY id');
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $teacherName = $row['teacher_id'] == 1 ? 'JAY MAR' : 'ROSEMARIE';
        echo "Module " . $row['id'] . ": " . $row['title'] . " | Section: " . $row['section'] . " | Teacher: " . $teacherName . "\n";
    }
    
    echo "\n=== EXPECTED RESULTS ===\n";
    echo "CHIBI (LEYNES) should now see:\n";
    echo "- Module created by ROSEMARIE for LEYNES section\n";
    echo "\nMA TERESA (LEGASPI) should see:\n";
    echo "- Modules created by ROSEMARIE (ENGLISH) and JAY MAR (AP) for LEGASPI section\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
