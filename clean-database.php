<?php
require_once 'config.php';

try {
    $db = new Database();
    $pdo = $db->getConnection();
    
    echo "Starting database cleanup...\n";
    
    // Delete all students
    $stmt = $pdo->prepare("DELETE FROM students");
    $stmt->execute();
    echo "✓ Deleted all students\n";
    
    // Delete all teachers
    $stmt = $pdo->prepare("DELETE FROM teachers");
    $stmt->execute();
    echo "✓ Deleted all teachers\n";
    
    // Delete all sections
    $stmt = $pdo->prepare("DELETE FROM sections");
    $stmt->execute();
    echo "✓ Deleted all sections\n";
    
    // Delete all teacher_sections
    $stmt = $pdo->prepare("DELETE FROM teacher_sections");
    $stmt->execute();
    echo "✓ Deleted all teacher-section relationships\n";
    
    // Reset auto-increment counters
    $pdo->exec("ALTER TABLE students AUTO_INCREMENT = 1");
    $pdo->exec("ALTER TABLE teachers AUTO_INCREMENT = 1");
    $pdo->exec("ALTER TABLE sections AUTO_INCREMENT = 1");
    $pdo->exec("ALTER TABLE teacher_sections AUTO_INCREMENT = 1");
    
    echo "✓ Reset auto-increment counters\n";
    echo "\nDatabase cleanup completed successfully!\n";
    
} catch (Exception $e) {
    echo "Error during cleanup: " . $e->getMessage() . "\n";
}
?>
