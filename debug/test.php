<?php
// test.php - Simple test script to verify PHP setup
echo "<!DOCTYPE html>\n";
echo "<html>\n<head>\n<title>PHP Test</title>\n</head>\n<body>\n";
echo "<h1>PHP Test Results</h1>\n";

// Test PHP version
echo "<h2>PHP Version</h2>\n";
echo "<p>PHP Version: " . phpversion() . "</p>\n";

// Test PDO SQLite
echo "<h2>PDO SQLite Test</h2>\n";
try {
    $pdo = new PDO('sqlite::memory:');
    echo "<p style='color: green;'>✅ PDO SQLite is working!</p>\n";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ PDO SQLite error: " . $e->getMessage() . "</p>\n";
}

// Test database connection
echo "<h2>Database Connection Test</h2>\n";
try {
    require_once 'config.php';
    echo "<p style='color: green;'>✅ Database connection successful!</p>\n";
    
    // Test table creation
    $stmt = $pdo->query("SELECT name FROM sqlite_master WHERE type='table'");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "<p>Tables created: " . implode(', ', $tables) . "</p>\n";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Database error: " . $e->getMessage() . "</p>\n";
}

// Test file permissions
echo "<h2>File Permissions Test</h2>\n";
$modulesDir = __DIR__ . '/public/modules';
if (is_writable($modulesDir)) {
    echo "<p style='color: green;'>✅ Modules directory is writable</p>\n";
} else {
    echo "<p style='color: orange;'>⚠️ Modules directory may not be writable</p>\n";
}

// Test session functionality
echo "<h2>Session Test</h2>\n";
if (session_start()) {
    echo "<p style='color: green;'>✅ Sessions are working</p>\n";
} else {
    echo "<p style='color: red;'>❌ Session error</p>\n";
}

echo "<h2>Next Steps</h2>\n";
echo "<ul>\n";
echo "<li><a href='public/login.html'>Student Login</a></li>\n";
echo "<li><a href='public/admin-login.html'>Admin Login</a></li>\n";
echo "<li><a href='public/admin-register.html'>Admin Registration</a></li>\n";
echo "</ul>\n";

echo "</body>\n</html>\n";
?>
