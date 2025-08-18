<?php
// fix-database-connection.php - Diagnose and fix database connection issues
echo "<h1>🔧 Database Connection Diagnostic & Fix</h1>";

echo "<h2>Step 1: PHP Configuration Check</h2>";

// Check if PHP extensions are loaded
$extensions = ['mysqli', 'pdo', 'pdo_mysql'];
$loadedExtensions = get_loaded_extensions();

echo "<h3>Required PHP Extensions:</h3>";
echo "<ul>";
foreach ($extensions as $ext) {
    $loaded = extension_loaded($ext);
    $status = $loaded ? "✅ LOADED" : "❌ NOT LOADED";
    $color = $loaded ? "green" : "red";
    echo "<li style='color: $color;'>$ext - $status</li>";
}
echo "</ul>";

// Check PDO drivers
echo "<h3>Available PDO Drivers:</h3>";
$drivers = PDO::getAvailableDrivers();
echo "<ul>";
foreach ($drivers as $driver) {
    echo "<li style='color: green;'>✅ $driver</li>";
}
echo "</ul>";

if (empty($drivers)) {
    echo "<p style='color: red;'>❌ No PDO drivers available!</p>";
}

// Check specific MySQL connection
echo "<h2>Step 2: MySQL Connection Test</h2>";

try {
    // Test basic MySQL connection without using config.php
    $testConnection = new PDO("mysql:host=localhost", "root", "");
    echo "<p style='color: green;'>✅ Basic MySQL connection successful</p>";
    
    // Test if we can create/access the database
    try {
        $testConnection->exec("CREATE DATABASE IF NOT EXISTS remediation_web CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        echo "<p style='color: green;'>✅ Database 'remediation_web' created/accessed successfully</p>";
        
        // Test connection to specific database
        $dbConnection = new PDO("mysql:host=localhost;dbname=remediation_web", "root", "");
        $dbConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        echo "<p style='color: green;'>✅ Connection to 'remediation_web' database successful</p>";
        
        // Test if we can create tables
        $dbConnection->exec("CREATE TABLE IF NOT EXISTS test_table (id INT PRIMARY KEY AUTO_INCREMENT, test_column VARCHAR(255))");
        echo "<p style='color: green;'>✅ Table creation test successful</p>";
        
        // Clean up test table
        $dbConnection->exec("DROP TABLE IF EXISTS test_table");
        
    } catch (PDOException $e) {
        echo "<p style='color: red;'>❌ Database access error: " . $e->getMessage() . "</p>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ MySQL connection failed: " . $e->getMessage() . "</p>";
    
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
    echo "<h3>🚨 Critical Issue: MySQL Not Running</h3>";
    echo "<p><strong>Immediate Actions Required:</strong></p>";
    echo "<ol>";
    echo "<li>Open XAMPP Control Panel</li>";
    echo "<li>Click 'Start' next to MySQL</li>";
    echo "<li>Wait until it shows 'Running' status</li>";
    echo "<li>Refresh this page to test again</li>";
    echo "</ol>";
    echo "</div>";
}

echo "<h2>Step 3: Config.php Test</h2>";

try {
    // Test our actual config
    require_once 'config.php';
    
    if (isset($pdo) && $pdo instanceof PDO) {
        echo "<p style='color: green;'>✅ Config.php database connection successful</p>";
        
        // Test a simple query
        $stmt = $pdo->query("SELECT 1 as test");
        $result = $stmt->fetch();
        if ($result['test'] == 1) {
            echo "<p style='color: green;'>✅ Database query test successful</p>";
        }
        
        // Check what tables exist
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        echo "<h3>Existing Tables:</h3>";
        if (empty($tables)) {
            echo "<p style='color: orange;'>⚠️ No tables found - will be created automatically</p>";
        } else {
            echo "<ul>";
            foreach ($tables as $table) {
                echo "<li style='color: green;'>✅ $table</li>";
            }
            echo "</ul>";
        }
        
    } else {
        echo "<p style='color: red;'>❌ Config.php failed to create database connection</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Config.php error: " . $e->getMessage() . "</p>";
}

echo "<h2>Step 4: API Endpoint Tests</h2>";

// Test critical API endpoints
$apiTests = [
    'admins.php' => 'Admin management',
    'students.php' => 'Student data (requires auth)',
    'modules.php' => 'Module data (requires auth)', 
    'admin-login.php' => 'Admin authentication'
];

echo "<h3>API Connectivity Tests:</h3>";
echo "<ul>";
foreach ($apiTests as $endpoint => $description) {
    $url = "/tms/remediation-web/api/$endpoint";
    echo "<li><a href='$url' target='_blank'>Test $endpoint ($description)</a></li>";
}
echo "</ul>";

echo "<h2>🛠️ Fix Instructions</h2>";

echo "<div style='background: #d1ecf1; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
echo "<h3>If Database Connection is Failing:</h3>";

echo "<h4>Option 1: Fix XAMPP MySQL (Recommended)</h4>";
echo "<ol>";
echo "<li><strong>Open XAMPP Control Panel</strong></li>";
echo "<li><strong>Start MySQL</strong> - Click 'Start' button next to MySQL</li>";
echo "<li><strong>Verify it's running</strong> - Should show green 'Running' status</li>";
echo "<li><strong>Test again</strong> - Refresh this page</li>";
echo "</ol>";

echo "<h4>Option 2: Check PHP Extensions</h4>";
echo "<ol>";
echo "<li>Open <code>xampp/php/php.ini</code></li>";
echo "<li>Find and uncomment these lines (remove ; at start):</li>";
echo "<ul>";
echo "<li><code>extension=mysqli</code></li>";
echo "<li><code>extension=pdo_mysql</code></li>";
echo "</ul>";
echo "<li>Restart Apache in XAMPP</li>";
echo "<li>Test again</li>";
echo "</ol>";

echo "<h4>Option 3: Use SQLite Instead</h4>";
echo "<ol>";
echo "<li><a href='switch-database.php?action=sqlite'>Switch to SQLite</a> (if available)</li>";
echo "<li>This uses file-based storage, no MySQL required</li>";
echo "</ol>";

echo "</div>";

echo "<h2>🎯 Next Steps After Fix</h2>";
echo "<ol>";
echo "<li>✅ Fix database connection (this step)</li>";
echo "<li>🧪 Test all APIs work correctly</li>";
echo "<li>👥 Test complete user registration/login flows</li>";
echo "<li>🔒 Verify security isolation between teachers</li>";
echo "<li>🚀 Prepare for production deployment</li>";
echo "</ol>";

echo "<hr>";
echo "<p><a href='prepare-for-production.php'>← Back to Production Readiness Check</a></p>";
?>
