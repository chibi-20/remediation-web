<?php
// auto-fix-php-extensions.php - Automatically fix PHP MySQL extensions
echo "<h1>🔧 Automatic PHP MySQL Extensions Fix</h1>";

// Check current status
echo "<h2>Current Status Check</h2>";
$mysqliLoaded = extension_loaded('mysqli');
$pdoMysqlLoaded = in_array('mysql', PDO::getAvailableDrivers());

echo "<p>mysqli extension: " . ($mysqliLoaded ? "✅ Loaded" : "❌ Not loaded") . "</p>";
echo "<p>pdo_mysql driver: " . ($pdoMysqlLoaded ? "✅ Loaded" : "❌ Not loaded") . "</p>";

if ($mysqliLoaded && $pdoMysqlLoaded) {
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px;'>";
    echo "<h3>✅ SUCCESS: Extensions are already loaded!</h3>";
    echo "<p>Your database connection should now work.</p>";
    echo "<p><a href='fix-database-connection.php'>→ Test Database Connection</a></p>";
    echo "</div>";
    exit;
}

echo "<h2>🛠️ Automatic Fix Attempt</h2>";

// Get PHP info to find configuration
ob_start();
phpinfo();
$phpinfo = ob_get_clean();

// Try to find XAMPP installation
$possiblePaths = [
    'C:\\xampp\\php\\php.ini',
    'C:\\xampp\\php\\php.ini-development',
    'C:\\xampp\\php\\php.ini-production',
    dirname(PHP_BINARY) . '\\php.ini',
    dirname(PHP_BINARY) . '\\php.ini-development'
];

echo "<h3>Searching for PHP configuration files...</h3>";
$phpIniPath = null;
$templatePath = null;

foreach ($possiblePaths as $path) {
    if (file_exists($path)) {
        echo "<p>✅ Found: $path</p>";
        if (strpos($path, 'php.ini') !== false && !strpos($path, '-')) {
            $phpIniPath = $path;
        } elseif (strpos($path, 'development') !== false) {
            $templatePath = $path;
        }
    } else {
        echo "<p>❌ Not found: $path</p>";
    }
}

// If no php.ini exists, create one from template
if (!$phpIniPath && $templatePath) {
    echo "<h3>Creating php.ini from template...</h3>";
    $phpIniPath = dirname($templatePath) . '\\php.ini';
    if (copy($templatePath, $phpIniPath)) {
        echo "<p>✅ Created php.ini from template: $phpIniPath</p>";
    } else {
        echo "<p>❌ Failed to create php.ini</p>";
    }
}

if ($phpIniPath && file_exists($phpIniPath)) {
    echo "<h3>Modifying php.ini to enable MySQL extensions...</h3>";
    
    // Read current php.ini
    $content = file_get_contents($phpIniPath);
    $original = $content;
    
    // Enable mysqli extension
    if (strpos($content, ';extension=mysqli') !== false) {
        $content = str_replace(';extension=mysqli', 'extension=mysqli', $content);
        echo "<p>✅ Enabled mysqli extension</p>";
    } elseif (strpos($content, 'extension=mysqli') === false) {
        $content .= "\n; MySQL Extensions\nextension=mysqli\n";
        echo "<p>✅ Added mysqli extension</p>";
    } else {
        echo "<p>ℹ️ mysqli already enabled</p>";
    }
    
    // Enable pdo_mysql extension
    if (strpos($content, ';extension=pdo_mysql') !== false) {
        $content = str_replace(';extension=pdo_mysql', 'extension=pdo_mysql', $content);
        echo "<p>✅ Enabled pdo_mysql extension</p>";
    } elseif (strpos($content, 'extension=pdo_mysql') === false) {
        $content .= "extension=pdo_mysql\n";
        echo "<p>✅ Added pdo_mysql extension</p>";
    } else {
        echo "<p>ℹ️ pdo_mysql already enabled</p>";
    }
    
    // Write back to file
    if ($content !== $original) {
        if (file_put_contents($phpIniPath, $content)) {
            echo "<p>✅ Successfully updated php.ini</p>";
            
            echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
            echo "<h3>⚠️ RESTART REQUIRED</h3>";
            echo "<p><strong>You must restart Apache in XAMPP for changes to take effect:</strong></p>";
            echo "<ol>";
            echo "<li>Open XAMPP Control Panel</li>";
            echo "<li>Stop Apache</li>";
            echo "<li>Start Apache</li>";
            echo "<li><a href='?refresh=1'>Refresh this page</a> to test</li>";
            echo "</ol>";
            echo "</div>";
            
        } else {
            echo "<p>❌ Failed to write to php.ini - check permissions</p>";
        }
    } else {
        echo "<p>ℹ️ No changes needed in php.ini</p>";
    }
    
} else {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px;'>";
    echo "<h3>❌ Manual Setup Required</h3>";
    echo "<p>Could not find or create php.ini automatically.</p>";
    echo "<p><strong>Manual steps:</strong></p>";
    echo "<ol>";
    echo "<li>Open XAMPP Control Panel</li>";
    echo "<li>Click 'Config' next to Apache → 'PHP (php.ini)'</li>";
    echo "<li>Find these lines and remove the semicolon (;):</li>";
    echo "<ul><li><code>;extension=mysqli</code> → <code>extension=mysqli</code></li>";
    echo "<li><code>;extension=pdo_mysql</code> → <code>extension=pdo_mysql</code></li></ul>";
    echo "<li>Save and restart Apache</li>";
    echo "</ol>";
    echo "</div>";
}

echo "<h2>🔍 Alternative: Check Extension Files</h2>";
$extDir = dirname(PHP_BINARY) . '\\ext\\';
$extFiles = ['php_mysqli.dll', 'php_pdo_mysql.dll'];

echo "<p>Extension directory: $extDir</p>";
foreach ($extFiles as $file) {
    $path = $extDir . $file;
    if (file_exists($path)) {
        echo "<p>✅ Extension file exists: $file</p>";
    } else {
        echo "<p>❌ Missing extension file: $file</p>";
    }
}

echo "<h2>🎯 Next Steps</h2>";
echo "<ol>";
echo "<li><strong>Restart Apache</strong> in XAMPP Control Panel</li>";
echo "<li><a href='?refresh=1'>Refresh this page</a> to check if extensions loaded</li>";
echo "<li><a href='fix-database-connection.php'>Test database connection</a></li>";
echo "<li>If still failing, <a href='DATABASE-FIX-GUIDE.md'>follow manual guide</a></li>";
echo "</ol>";
?>
