<?php
require_once 'config.php';

try {
    $db = new Database();
    $pdo = $db->getConnection();
    
    // Create a proper password hash for "password123"
    $hashedPassword = password_hash('password123', PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare('UPDATE admins SET password = ? WHERE id = 2');
    $stmt->execute([$hashedPassword]);
    
    echo "Updated teacher ID 2 password to 'password123'\n";
    echo "Username: teacher2\n";
    echo "Password: password123\n";
    
    // Test the login
    echo "\n=== TESTING LOGIN ===\n";
    $testLogin = password_verify('password123', $hashedPassword);
    echo "Password verification test: " . ($testLogin ? "✅ PASS" : "❌ FAIL") . "\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
