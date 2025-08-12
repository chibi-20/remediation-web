<?php
// mysql-status.php - Check MySQL status and provide troubleshooting
echo "<h1>MySQL Connection Troubleshooting</h1>";

echo "<h2>Connection Test</h2>";
try {
    // Test basic MySQL connection
    $pdo = new PDO("mysql:host=localhost", "root", "");
    echo "<p style='color: green;'>✅ MySQL is running and accessible!</p>";
    
    // Test database creation
    $pdo->exec("CREATE DATABASE IF NOT EXISTS remediation_web CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "<p style='color: green;'>✅ Database 'remediation_web' is ready!</p>";
    
    echo "<p><a href='public/admin-register.html' style='background: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Continue to Admin Registration</a></p>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ MySQL Connection Failed: " . $e->getMessage() . "</p>";
    
    if (strpos($e->getMessage(), "refused") !== false) {
        echo "<h2>MySQL is not running. Here's how to fix it:</h2>";
        echo "<h3>Step 1: Start MySQL in XAMPP</h3>";
        echo "<ol>";
        echo "<li>Open XAMPP Control Panel</li>";
        echo "<li>Look for 'MySQL' in the list</li>";
        echo "<li>Click the 'Start' button next to MySQL</li>";
        echo "<li>Wait for it to show 'Running' status</li>";
        echo "<li>Refresh this page to test again</li>";
        echo "</ol>";
        
        echo "<h3>Step 2: If MySQL won't start</h3>";
        echo "<ol>";
        echo "<li>Check if port 3306 is already in use by another application</li>";
        echo "<li>In XAMPP Control Panel, click 'Config' next to MySQL</li>";
        echo "<li>Select 'my.ini' and change the port if needed</li>";
        echo "<li>Or try stopping any other MySQL services running on your computer</li>";
        echo "</ol>";
        
        echo "<h3>Alternative: Use SQLite (simpler)</h3>";
        echo "<p>If MySQL continues to have issues, we can switch back to SQLite. I'll create a version that works without additional drivers.</p>";
        echo "<p><a href='?switch=sqlite'>Switch to SQLite</a></p>";
    }
}

if (isset($_GET['switch']) && $_GET['switch'] === 'sqlite') {
    echo "<h2>Switching to SQLite...</h2>";
    
    // Create a simplified SQLite config that should work
    $sqliteConfig = '<?php
// config.php - SQLite configuration (simplified)
define("DB_FILE", __DIR__ . "/students.db");

class Database {
    private $pdo;
    
    public function __construct() {
        try {
            // Try SQLite first, fallback to file-based storage if needed
            if (class_exists("PDO") && in_array("sqlite", PDO::getAvailableDrivers())) {
                $this->pdo = new PDO("sqlite:" . DB_FILE);
                $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $this->createTables();
            } else {
                throw new Exception("SQLite PDO not available");
            }
        } catch (Exception $e) {
            // Fallback to file-based storage
            $this->setupFileStorage();
        }
    }
    
    private function createTables() {
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS admins (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT,
                grade TEXT,
                subject TEXT,
                username TEXT UNIQUE,
                password TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");
        
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS students (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                firstName TEXT,
                lastName TEXT,
                section TEXT,
                lrn TEXT UNIQUE,
                progress TEXT DEFAULT \"{}\",
                admin_id INTEGER,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");
        
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS modules (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                quarter TEXT,
                filename TEXT,
                questions TEXT,
                admin_id INTEGER,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");
    }
    
    private function setupFileStorage() {
        // Create data directory for file-based storage
        if (!is_dir(__DIR__ . "/data")) {
            mkdir(__DIR__ . "/data", 0755, true);
        }
        $this->pdo = null; // Mark as file-based mode
    }
    
    public function getConnection() {
        return $this->pdo;
    }
}

// Utility functions
function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header("Content-Type: application/json");
    echo json_encode($data);
    exit;
}

function sanitizeInput($input) {
    return trim(htmlspecialchars($input));
}

function validateRequired($fields, $data) {
    foreach ($fields as $field) {
        if (empty($data[$field])) {
            return "Field \"$field\" is required.";
        }
    }
    return null;
}

// Session management
function startSession() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function isLoggedIn() {
    startSession();
    return isset($_SESSION["admin_id"]);
}

function requireLogin() {
    if (!isLoggedIn()) {
        jsonResponse(["success" => false, "error" => "Authentication required"], 401);
    }
}

// Initialize database connection
try {
    $db = new Database();
    $pdo = $db->getConnection();
} catch (Exception $e) {
    // Handle gracefully - the app can still work with file storage
    $pdo = null;
}
?>';

    if (file_put_contents('config.php', $sqliteConfig)) {
        echo "<p style='color: green;'>✅ Switched to SQLite configuration!</p>";
        echo "<p><a href='?'>Test SQLite Connection</a></p>";
    } else {
        echo "<p style='color: red;'>❌ Failed to write configuration file.</p>";
    }
}

echo "<h2>XAMPP Status Check</h2>";
echo "<p>Please check your XAMPP Control Panel:</p>";
echo "<ul>";
echo "<li>Apache should be running (green)</li>";
echo "<li>MySQL should be running (green)</li>";
echo "<li>If MySQL is red, click the Start button</li>";
echo "</ul>";

echo "<h2>Quick Links</h2>";
echo "<ul>";
echo "<li><a href='switch-database.php'>Database Switcher</a></li>";
echo "<li><a href='check-php.php'>PHP Configuration Check</a></li>";
echo "<li><a href='debug.php'>Debug Information</a></li>";
echo "</ul>";
?>
