<?php
// Update existing modules to show current structure
require_once 'config.php';

try {
    $db = new Database();
    $pdo = $db->getConnection();
    
    echo "=== CURRENT MODULES AFTER CLEANUP ===\n";
    $stmt = $pdo->query("SELECT id, title, section, teacher_id FROM modules");
    $modules = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($modules as $module) {
        echo "Module ID: {$module['id']}\n";
        echo "Title: {$module['title']}\n";
        echo "Section(s): {$module['section']}\n";
        echo "Teacher ID: {$module['teacher_id']}\n";
        echo "---\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
