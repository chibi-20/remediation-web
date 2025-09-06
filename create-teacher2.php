<?php
require_once 'config.php';

try {
    $db = new Database();
    $pdo = $db->getConnection();
    
    // Check if there's a teachers table
    $stmt = $pdo->query('SHOW TABLES');
    $tables = [];
    while ($row = $stmt->fetch()) {
        $tables[] = $row[0];
    }
    
    echo "=== ALL TABLES ===\n";
    foreach ($tables as $table) {
        echo $table . "\n";
    }
    
    // Check if there's a separate teachers table
    if (in_array('teachers', $tables)) {
        echo "\n=== TEACHERS TABLE ===\n";
        $stmt = $pdo->query('SELECT * FROM teachers');
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            print_r($row);
        }
    }
    
    // Let's create a teacher with ID 2 in the admins table
    echo "\n=== CREATING TEACHER ID 2 ===\n";
    $stmt = $pdo->prepare('INSERT INTO admins (id, username, password, name) VALUES (2, "teacher2", "$2y$10$example", "Test Teacher") ON DUPLICATE KEY UPDATE name = "Test Teacher"');
    $stmt->execute();
    echo "Created/updated teacher ID 2\n";
    
    // Verify
    echo "\n=== UPDATED ADMINS ===\n";
    $stmt = $pdo->query('SELECT * FROM admins');
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "ID: " . $row['id'] . " | Username: " . $row['username'] . " | Name: " . $row['name'] . "\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
