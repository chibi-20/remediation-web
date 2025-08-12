<?php
// test-admins-api.php - Test the admins API endpoint
echo "<h1>Testing Admins API</h1>";

echo "<h2>Direct File Test</h2>";
if (file_exists('api/admins.php')) {
    echo "<p style='color: green;'>✅ api/admins.php exists</p>";
} else {
    echo "<p style='color: red;'>❌ api/admins.php not found</p>";
}

echo "<h2>API Response Test</h2>";
try {
    // Test the API directly
    $url = 'http://localhost/tms/remediation-web/api/admins.php';
    
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => 'Content-Type: application/json'
        ]
    ]);
    
    $response = file_get_contents($url, false, $context);
    
    if ($response) {
        echo "<p style='color: green;'>✅ API Response received:</p>";
        echo "<pre>" . htmlspecialchars($response) . "</pre>";
        
        $json = json_decode($response, true);
        if ($json !== null) {
            echo "<p style='color: green;'>✅ Valid JSON response</p>";
            echo "<p>Admin count: " . count($json) . "</p>";
        } else {
            echo "<p style='color: red;'>❌ Invalid JSON response</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ No response from API</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}

echo "<h2>Database Check</h2>";
try {
    require_once 'config.php';
    
    $stmt = $pdo->prepare("SELECT id, name, username FROM admins");
    $stmt->execute();
    $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($admins)) {
        echo "<p style='color: orange;'>⚠️ No admin accounts found in database</p>";
        echo "<p>You need to register an admin first</p>";
        echo "<p><a href='public/admin-register.html'>Register Admin Account</a></p>";
    } else {
        echo "<p style='color: green;'>✅ Found " . count($admins) . " admin(s) in database:</p>";
        echo "<ul>";
        foreach ($admins as $admin) {
            echo "<li>ID: {$admin['id']}, Username: {$admin['username']}, Name: {$admin['name']}</li>";
        }
        echo "</ul>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Database error: " . $e->getMessage() . "</p>";
}

echo "<h2>Quick Fix</h2>";
echo "<p>If the API is still not working, try these solutions:</p>";
echo "<ul>";
echo "<li><a href='?fix=recreate'>Recreate admins.php file</a></li>";
echo "<li><a href='public/admin-register.html'>Register an admin account first</a></li>";
echo "<li><a href='debug.php'>Debug database connection</a></li>";
echo "</ul>";

if (isset($_GET['fix']) && $_GET['fix'] === 'recreate') {
    echo "<h3>Recreating admins.php...</h3>";
    
    $adminsPhp = '<?php
// api/admins.php - Get all admins endpoint
require_once "../config.php";

header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    jsonResponse(["success" => false, "error" => "Method not allowed"], 405);
}

try {
    $stmt = $pdo->prepare("SELECT id, name, grade, subject, username FROM admins");
    $stmt->execute();
    $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    jsonResponse($admins);
    
} catch (PDOException $e) {
    jsonResponse(["error" => "Database error"], 500);
}
?>';

    if (file_put_contents('api/admins.php', $adminsPhp)) {
        echo "<p style='color: green;'>✅ admins.php recreated successfully!</p>";
        echo "<p><a href='?'>Test again</a></p>";
    } else {
        echo "<p style='color: red;'>❌ Failed to recreate file</p>";
    }
}
?>
