<?php
// debug-routes.php - Debug routing issues
echo "<h1>Debug Routing</h1>";

echo "<h2>Request Information</h2>";
echo "<p>REQUEST_URI: " . $_SERVER['REQUEST_URI'] . "</p>";
echo "<p>REQUEST_METHOD: " . $_SERVER['REQUEST_METHOD'] . "</p>";
echo "<p>PATH_INFO: " . ($_SERVER['PATH_INFO'] ?? 'Not set') . "</p>";
echo "<p>SCRIPT_NAME: " . $_SERVER['SCRIPT_NAME'] . "</p>";

$request = $_SERVER['REQUEST_URI'];
$path = parse_url($request, PHP_URL_PATH);
echo "<p>Parsed path: " . $path . "</p>";

// Remove base path if running in subdirectory
$basePath = '/tms/remediation-web';
if (strpos($path, $basePath) === 0) {
    $path = substr($path, strlen($basePath));
    echo "<p>Path after removing base: " . $path . "</p>";
}

echo "<h2>Test Routes</h2>";
echo "<ul>";
echo "<li><a href='/tms/remediation-web/admin-login'>Test /admin-login (should work for POST)</a></li>";
echo "<li><a href='/tms/remediation-web/api/register-admin'>Test /api/register-admin (should work for POST)</a></li>";
echo "<li><a href='/tms/remediation-web/debug.php'>Debug Database</a></li>";
echo "<li><a href='/tms/remediation-web/migrate-db.php'>Migrate Database</a></li>";
echo "</ul>";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<h2>POST Data</h2>";
    echo "<p>Raw input: " . htmlspecialchars(file_get_contents('php://input')) . "</p>";
}
?>
