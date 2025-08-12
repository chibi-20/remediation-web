<?php
// check-php.php - Check PHP configuration
echo "<h1>PHP Configuration Check</h1>";

echo "<h2>PHP Version</h2>";
echo "<p>Version: " . phpversion() . "</p>";

echo "<h2>Required Extensions</h2>";

$required = ['pdo', 'pdo_sqlite', 'sqlite3'];
$missing = [];

foreach ($required as $ext) {
    if (extension_loaded($ext)) {
        echo "<p style='color: green;'>✅ $ext is loaded</p>";
    } else {
        echo "<p style='color: red;'>❌ $ext is NOT loaded</p>";
        $missing[] = $ext;
    }
}

if (!empty($missing)) {
    echo "<h2>How to Fix Missing Extensions</h2>";
    echo "<h3>For XAMPP:</h3>";
    echo "<ol>";
    echo "<li>Open XAMPP Control Panel</li>";
    echo "<li>Click 'Config' next to Apache</li>";
    echo "<li>Select 'PHP (php.ini)'</li>";
    echo "<li>Find and uncomment these lines (remove the semicolon):</li>";
    echo "<ul>";
    if (in_array('pdo', $missing)) echo "<li><code>extension=pdo</code></li>";
    if (in_array('pdo_sqlite', $missing)) echo "<li><code>extension=pdo_sqlite</code></li>";
    if (in_array('sqlite3', $missing)) echo "<li><code>extension=sqlite3</code></li>";
    echo "</ul>";
    echo "<li>Save the file</li>";
    echo "<li>Restart Apache in XAMPP</li>";
    echo "</ol>";
    
    echo "<h3>Alternative: Use MySQL</h3>";
    echo "<p>If SQLite continues to have issues, we can convert the project to use MySQL (which is included in XAMPP by default).</p>";
} else {
    echo "<p style='color: green; font-weight: bold;'>✅ All required extensions are loaded!</p>";
    echo "<p><a href='migrate-db.php'>Proceed with database migration</a></p>";
}

echo "<h2>All Loaded Extensions</h2>";
$extensions = get_loaded_extensions();
echo "<p>Count: " . count($extensions) . "</p>";
echo "<details><summary>View all extensions</summary>";
echo "<ul>";
foreach ($extensions as $ext) {
    echo "<li>$ext</li>";
}
echo "</ul></details>";

echo "<h2>PHP Info</h2>";
echo "<p><a href='phpinfo.php' target='_blank'>View full PHP info</a></p>";
?>
