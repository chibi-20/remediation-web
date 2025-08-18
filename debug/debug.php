<?php
// debug.php - Debug script to check database state
require_once 'config.php';

echo "<h1>Database Debug Information</h1>";

try {
    // Check if admins table exists and has data
    echo "<h2>Admins Table</h2>";
    $stmt = $pdo->query("SELECT * FROM admins");
    $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($admins)) {
        echo "<p style='color: orange;'>⚠️ No admin accounts found. You need to register an admin first.</p>";
        echo "<p><a href='public/admin-register.html'>Register Admin Account</a></p>";
    } else {
        echo "<p style='color: green;'>✅ Found " . count($admins) . " admin account(s):</p>";
        echo "<ul>";
        foreach ($admins as $admin) {
            echo "<li>ID: {$admin['id']}, Username: {$admin['username']}</li>";
        }
        echo "</ul>";
    }
    
    // Check students table
    echo "<h2>Students Table</h2>";
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM students");
    $studentCount = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<p>Students count: " . $studentCount['count'] . "</p>";
    
    // Check modules table
    echo "<h2>Modules Table</h2>";
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM modules");
    $moduleCount = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<p>Modules count: " . $moduleCount['count'] . "</p>";
    
    // Check if request is working
    echo "<h2>Request Information</h2>";
    echo "<p>Request Method: " . $_SERVER['REQUEST_METHOD'] . "</p>";
    echo "<p>Request URI: " . $_SERVER['REQUEST_URI'] . "</p>";
    echo "<p>PHP Version: " . phpversion() . "</p>";
    
    // Test JSON input parsing
    echo "<h2>Test JSON Input</h2>";
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = file_get_contents('php://input');
        echo "<p>Raw input: " . htmlspecialchars($input) . "</p>";
        $decoded = json_decode($input, true);
        echo "<p>Decoded JSON: " . print_r($decoded, true) . "</p>";
    } else {
        echo "<p>Make a POST request to test JSON parsing</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

echo "<h2>Next Steps</h2>";
echo "<ul>";
echo "<li><a href='public/admin-register.html'>Register Admin Account</a></li>";
echo "<li><a href='public/admin-login.html'>Admin Login</a></li>";
echo "<li><a href='test.php'>Run System Test</a></li>";
echo "</ul>";
?>
