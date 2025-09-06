<?php
require_once 'config.php';

try {
    $db = new Database();
    $pdo = $db->getConnection();
    
    // Check assessments count
    $stmt = $pdo->query('SELECT COUNT(*) as count FROM assessments');
    $row = $stmt->fetch();
    echo "Total assessments: " . $row['count'] . "\n";
    
    if ($row['count'] > 0) {
        echo "\n=== ASSESSMENT RECORDS ===\n";
        $stmt = $pdo->query('SELECT * FROM assessments LIMIT 10');
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            print_r($row);
        }
    }
    
    // Check students table
    echo "\n=== STUDENTS BY SECTION ===\n";
    $stmt = $pdo->query('SELECT section, COUNT(*) as count FROM students GROUP BY section');
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "Section: " . $row['section'] . " - Students: " . $row['count'] . "\n";
    }
    
    // Check modules with sections
    echo "\n=== MODULES WITH SECTIONS ===\n";
    $stmt = $pdo->query('SELECT id, title, sections FROM modules');
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "Module " . $row['id'] . ": " . $row['title'] . " - Sections: " . $row['sections'] . "\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
