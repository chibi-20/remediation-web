<?php
// Fix student admin_id
require_once '../config.php';

try {
    // Update the student to assign them to admin_id = 1
    $stmt = $pdo->prepare("UPDATE students SET admin_id = 1 WHERE lrn = '101010101010'");
    $stmt->execute();
    
    echo "Student admin_id updated successfully!<br>";
    
    // Verify the update
    $stmt = $pdo->prepare("SELECT * FROM students WHERE lrn = '101010101010'");
    $stmt->execute();
    $student = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<h3>Updated Student Data:</h3>";
    echo "<pre>";
    print_r($student);
    echo "</pre>";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
