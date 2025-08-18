<?php
// direct-test.php - Test API files directly
echo "<h1>Direct API Test</h1>";

echo "<h2>Testing register-admin.php directly</h2>";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<h3>POST Request Received</h3>";
    echo "<p>Testing the register-admin API directly...</p>";
    
    // Simulate the API call
    $_SERVER['REQUEST_METHOD'] = 'POST';
    
    // Capture output
    ob_start();
    try {
        require 'api/register-admin.php';
        $output = ob_get_contents();
        echo "<p style='color: green;'>✅ API executed successfully</p>";
        echo "<p>Output: <code>" . htmlspecialchars($output) . "</code></p>";
    } catch (Exception $e) {
        $output = ob_get_contents();
        echo "<p style='color: red;'>❌ API error: " . $e->getMessage() . "</p>";
        echo "<p>Output: <code>" . htmlspecialchars($output) . "</code></p>";
    }
    ob_end_clean();
} else {
    echo "<p>This page tests the API directly. Click the button below to test:</p>";
    echo "<form method='POST'>";
    echo "<button type='submit'>Test Register Admin API</button>";
    echo "</form>";
}

echo "<h2>Alternative: Frontend Fix</h2>";
echo "<p>If routing continues to be problematic, we can modify the frontend to use direct paths:</p>";

echo "<h3>Option 1: Use direct API paths</h3>";
echo "<p>Change the frontend to call: <code>/tms/remediation-web/api/register-admin.php</code></p>";

echo "<h3>Option 2: Test without .htaccess</h3>";
echo "<p>Temporarily rename .htaccess to see if it's causing issues</p>";

if (isset($_GET['action'])) {
    if ($_GET['action'] === 'disable-htaccess' && file_exists('.htaccess')) {
        if (rename('.htaccess', '.htaccess.backup')) {
            echo "<p style='color: green;'>✅ .htaccess disabled (renamed to .htaccess.backup)</p>";
            echo "<p>Now test the registration form again</p>";
        }
    } elseif ($_GET['action'] === 'enable-htaccess' && file_exists('.htaccess.backup')) {
        if (rename('.htaccess.backup', '.htaccess')) {
            echo "<p style='color: green;'>✅ .htaccess re-enabled</p>";
        }
    }
}

echo "<p><a href='?action=disable-htaccess'>Disable .htaccess temporarily</a> | ";
echo "<a href='?action=enable-htaccess'>Re-enable .htaccess</a></p>";

echo "<h2>Quick Links</h2>";
echo "<ul>";
echo "<li><a href='test-routes.php'>Full Route Test</a></li>";
echo "<li><a href='public/admin-register.html'>Try Admin Registration</a></li>";
echo "<li><a href='index.php'>Main Application</a></li>";
echo "</ul>";
?>
