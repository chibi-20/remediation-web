<?php
// switch-database.php - Switch between SQLite and MySQL
echo "<h1>Database Configuration Switcher</h1>";

$action = $_GET['action'] ?? '';

if ($action === 'mysql') {
    // Copy MySQL config to main config
    if (copy('config-mysql.php', 'config.php')) {
        echo "<p style='color: green;'>✅ Switched to MySQL configuration!</p>";
        echo "<p>Please make sure MySQL is running in XAMPP.</p>";
    } else {
        echo "<p style='color: red;'>❌ Failed to switch configuration.</p>";
    }
} elseif ($action === 'test') {
    // Test current configuration
    echo "<h2>Testing Current Configuration</h2>";
    try {
        require_once 'config.php';
        echo "<p style='color: green;'>✅ Database connection successful!</p>";
        
        // Check if we're using MySQL or SQLite
        if (isset($pdo) && $pdo) {
            $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
            echo "<p>Database type: " . strtoupper($driver) . "</p>";
            
            if ($driver === 'mysql') {
                $stmt = $pdo->query("SHOW TABLES");
                $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
                echo "<p>Tables created: " . (empty($tables) ? 'None yet' : implode(', ', $tables)) . "</p>";
            } else {
                $stmt = $pdo->query("SELECT name FROM sqlite_master WHERE type='table'");
                $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
                echo "<p>Tables created: " . (empty($tables) ? 'None yet' : implode(', ', $tables)) . "</p>";
            }
        } else {
            echo "<p style='color: orange;'>Using file-based storage (no database)</p>";
        }
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Database error: " . $e->getMessage() . "</p>";
        
        if (strpos($e->getMessage(), "refused") !== false) {
            echo "<h3>MySQL is not running!</h3>";
            echo "<p><strong>Solution:</strong></p>";
            echo "<ol>";
            echo "<li>Open XAMPP Control Panel</li>";
            echo "<li>Click 'Start' next to MySQL</li>";
            echo "<li>Wait for it to show 'Running'</li>";
            echo "<li>Test again</li>";
            echo "</ol>";
            echo "<p><a href='mysql-status.php'>Detailed MySQL Troubleshooting</a></p>";
        }
    }
}

echo "<h2>Available Options</h2>";
echo "<ul>";
echo "<li><a href='?action=mysql'>Switch to MySQL (Recommended for XAMPP)</a></li>";
echo "<li><a href='?action=test'>Test Current Configuration</a></li>";
echo "</ul>";

echo "<h2>Current Configuration</h2>";
if (file_exists('config.php')) {
    $config = file_get_contents('config.php');
    if (strpos($config, 'mysql:') !== false) {
        echo "<p style='color: green;'>Currently using: MySQL</p>";
    } elseif (strpos($config, 'sqlite:') !== false) {
        echo "<p style='color: blue;'>Currently using: SQLite</p>";
    } else {
        echo "<p style='color: orange;'>Unknown configuration</p>";
    }
} else {
    echo "<p style='color: red;'>No configuration file found</p>";
}

echo "<h2>MySQL Setup Instructions</h2>";
echo "<ol>";
echo "<li>Open XAMPP Control Panel</li>";
echo "<li>Start Apache (if not running)</li>";
echo "<li>Start MySQL</li>";
echo "<li>Click 'Switch to MySQL' above</li>";
echo "<li>Test the configuration</li>";
echo "</ol>";

echo "<h2>Next Steps</h2>";
echo "<ul>";
echo "<li><a href='public/admin-register.html'>Register Admin Account</a></li>";
echo "<li><a href='public/admin-login.html'>Admin Login</a></li>";
echo "<li><a href='debug.php'>Debug Information</a></li>";
echo "</ul>";
?>
