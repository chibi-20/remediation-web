<?php
// Create teacher-section relationship table
require_once '../config.php';

try {
    // Create teacher_sections table to manage teacher-section relationships
    $sql = "CREATE TABLE IF NOT EXISTS teacher_sections (
        id INT AUTO_INCREMENT PRIMARY KEY,
        admin_id INT NOT NULL,
        section VARCHAR(100) NOT NULL,
        role ENUM('adviser', 'subject_teacher') DEFAULT 'subject_teacher',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE CASCADE,
        UNIQUE KEY unique_teacher_section (admin_id, section)
    )";
    
    $pdo->exec($sql);
    echo "✅ teacher_sections table created successfully!<br>";
    
    // Add some sample relationships
    $stmt = $pdo->prepare("INSERT IGNORE INTO teacher_sections (admin_id, section, role) VALUES (?, ?, ?)");
    
    // Jay Mar as adviser of LEYNES
    $stmt->execute([1, 'LEYNES', 'adviser']);
    echo "✅ Added Jay Mar as adviser of LEYNES<br>";
    
    // Example: Add Rosemarie as subject teacher (when she registers)
    // This would be done during admin registration or through admin panel
    
    echo "<br>Teacher-Section relationship system is ready!";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
