<?php
// test-admin-login.php - Debug admin login
require_once '../config.php';

echo "<h3>🔍 Testing Admin Login</h3>";

// Check if master admin exists
$stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
$stmt->execute(['307901']);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

if ($admin) {
    echo "✅ Master admin found:<br>";
    echo "ID: {$admin['id']}<br>";
    echo "Name: {$admin['name']}<br>";
    echo "Username: {$admin['username']}<br>";
    echo "Created: {$admin['created_at']}<br>";
    
    // Test password
    if (password_verify('ilovejacobo', $admin['password'])) {
        echo "✅ Password verification: SUCCESS<br>";
    } else {
        echo "❌ Password verification: FAILED<br>";
    }
} else {
    echo "❌ Master admin not found!<br>";
}

echo "<hr>";
echo "<h4>All Admin Accounts:</h4>";
$stmt = $pdo->prepare("SELECT id, name, username, created_at FROM admins ORDER BY id");
$stmt->execute();
$admins = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($admins as $admin) {
    echo "• ID: {$admin['id']} | {$admin['name']} | Username: {$admin['username']}<br>";
}

echo "<hr>";
echo "<p><a href='../public/teacher-login.html'>🔐 Go to Admin Login</a></p>";
echo "<p><a href='../public/index.html'>🏠 Go to Home Page</a></p>";
?>
