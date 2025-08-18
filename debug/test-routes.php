<?php
// test-routes.php - Test API routing
echo "<h1>API Routes Test</h1>";

echo "<h2>Test API Endpoints</h2>";

$baseUrl = 'http://localhost/tms/remediation-web';

echo "<h3>Test 1: Register Admin (POST)</h3>";
echo "<p>Testing: <code>POST {$baseUrl}/api/register-admin</code></p>";

$testData = json_encode([
    'name' => 'Test Teacher',
    'grade' => 'Grade 10',
    'subject' => 'Test Subject',
    'username' => 'testuser' . time(),
    'password' => 'testpass123'
]);

$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/json',
        'content' => $testData
    ]
]);

try {
    $response = file_get_contents("{$baseUrl}/api/register-admin", false, $context);
    $result = json_decode($response, true);
    
    if ($result) {
        echo "<p style='color: green;'>✅ API Response: " . json_encode($result) . "</p>";
    } else {
        echo "<p style='color: red;'>❌ Invalid JSON response:</p>";
        echo "<pre>" . htmlspecialchars($response) . "</pre>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

echo "<h3>Test 2: Direct File Access</h3>";
echo "<p>Testing direct access to API file...</p>";

if (file_exists('api/register-admin.php')) {
    echo "<p style='color: green;'>✅ API file exists</p>";
} else {
    echo "<p style='color: red;'>❌ API file missing</p>";
}

echo "<h3>Test 3: .htaccess Check</h3>";
if (file_exists('.htaccess')) {
    echo "<p style='color: green;'>✅ .htaccess file exists</p>";
    echo "<details><summary>View .htaccess content</summary>";
    echo "<pre>" . htmlspecialchars(file_get_contents('.htaccess')) . "</pre>";
    echo "</details>";
} else {
    echo "<p style='color: red;'>❌ .htaccess file missing</p>";
}

echo "<h3>Test 4: Apache mod_rewrite</h3>";
if (function_exists('apache_get_modules')) {
    $modules = apache_get_modules();
    if (in_array('mod_rewrite', $modules)) {
        echo "<p style='color: green;'>✅ mod_rewrite is enabled</p>";
    } else {
        echo "<p style='color: red;'>❌ mod_rewrite is NOT enabled</p>";
    }
} else {
    echo "<p style='color: orange;'>⚠️ Cannot check Apache modules (not running under Apache or function not available)</p>";
}

echo "<h2>Debug Information</h2>";
echo "<p>Current URL: " . $_SERVER['REQUEST_URI'] . "</p>";
echo "<p>Server Software: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . "</p>";

echo "<h2>Quick Fixes</h2>";
echo "<ul>";
echo "<li><a href='?fix=htaccess'>Recreate .htaccess file</a></li>";
echo "<li><a href='direct-test.php'>Test direct API access</a></li>";
echo "</ul>";

if (isset($_GET['fix']) && $_GET['fix'] === 'htaccess') {
    echo "<h3>Recreating .htaccess file...</h3>";
    
    $htaccessContent = 'RewriteEngine On

# Redirect all requests to index.php if the file/directory doesn\'t exist
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]

# Set up proper MIME types
AddType application/javascript .js
AddType text/css .css
AddType application/pdf .pdf

# Security headers
Header always set X-Content-Type-Options nosniff
Header always set X-Frame-Options DENY
Header always set X-XSS-Protection "1; mode=block"';

    if (file_put_contents('.htaccess', $htaccessContent)) {
        echo "<p style='color: green;'>✅ .htaccess file recreated!</p>";
    } else {
        echo "<p style='color: red;'>❌ Failed to create .htaccess file</p>";
    }
}
?>
