<?php
// config.php - Environment-aware configuration
require_once __DIR__ . '/env-loader.php';

// Database configuration from environment
define('DB_HOST', EnvLoader::get('DB_HOST', 'localhost'));
define('DB_NAME', EnvLoader::get('DB_NAME', 'remediation_web'));
define('DB_USER', EnvLoader::get('DB_USER', 'root'));
define('DB_PASS', EnvLoader::get('DB_PASS', ''));

// Application configuration
define('APP_NAME', EnvLoader::get('APP_NAME', 'SAGIP ARAL - Remediation Learning System'));
define('APP_VERSION', EnvLoader::get('APP_VERSION', '1.0.0'));
define('BASE_URL', EnvLoader::get('BASE_URL', 'http://localhost/tms/remediation-web'));
define('UPLOAD_PATH', EnvLoader::get('UPLOAD_PATH', 'public/MODULES/'));
define('MAX_FILE_SIZE', (int)EnvLoader::get('MAX_FILE_SIZE', 10485760));

// Security configuration
define('SESSION_LIFETIME', (int)EnvLoader::get('SESSION_LIFETIME', 3600));
define('SECURE_COOKIES', filter_var(EnvLoader::get('SECURE_COOKIES', false), FILTER_VALIDATE_BOOLEAN));

class Database {
    private $pdo;
    
    public function __construct() {
        try {
            // First, try to connect without database to create it if needed
            $pdo_temp = new PDO("mysql:host=" . DB_HOST, DB_USER, DB_PASS);
            $pdo_temp->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Create database if it doesn't exist
            $pdo_temp->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            
            // Now connect to the specific database
            $this->pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            
            $this->createTables();
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }
    
    private function createTables() {
        // Create admins table
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS admins (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255),
                grade VARCHAR(100),
                subject VARCHAR(255),
                sections TEXT,
                username VARCHAR(100) UNIQUE,
                password VARCHAR(255),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");
        
        // Create students table
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS students (
                id INT AUTO_INCREMENT PRIMARY KEY,
                firstName VARCHAR(255),
                lastName VARCHAR(255),
                section VARCHAR(255),
                lrn VARCHAR(12) UNIQUE,
                progress TEXT,
                admin_id INT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE SET NULL
            )
        ");
        
        // Create modules table
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS modules (
                id INT AUTO_INCREMENT PRIMARY KEY,
                quarter VARCHAR(100),
                filename VARCHAR(255),
                questions TEXT,
                admin_id INT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE CASCADE
            )
        ");
    }
    
    public function getConnection() {
        return $this->pdo;
    }
}

// Utility functions
function jsonResponse($success, $message = '', $data = null, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    
    $response = [
        'success' => $success,
        'message' => $message
    ];
    
    if ($data !== null) {
        $response['data'] = $data;
    }
    
    echo json_encode($response);
    exit;
}

function sanitizeInput($input) {
    return trim(htmlspecialchars($input));
}

function validateRequired($fields, $data) {
    foreach ($fields as $field) {
        if (empty($data[$field])) {
            return "Field '$field' is required.";
        }
    }
    return null;
}

// Session management with environment-based configuration
function startSession() {
    if (session_status() === PHP_SESSION_NONE) {
        // Configure session based on environment
        if (SECURE_COOKIES && isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            ini_set('session.cookie_secure', 1);
            ini_set('session.cookie_httponly', 1);
            ini_set('session.cookie_samesite', 'Strict');
        } else if (SECURE_COOKIES) {
            // If SECURE_COOKIES is true but we're not on HTTPS, log a warning
            error_log('Warning: SECURE_COOKIES enabled but HTTPS not detected');
            ini_set('session.cookie_httponly', 1);
            ini_set('session.cookie_samesite', 'Lax');
        }
        
        ini_set('session.gc_maxlifetime', SESSION_LIFETIME);
        ini_set('session.cookie_lifetime', SESSION_LIFETIME);
        
        // Set session name to something more specific
        session_name('REMEDIATION_SESSION');
        
        session_start();
        
        // Regenerate session ID periodically for security
        if (!isset($_SESSION['last_regeneration'])) {
            $_SESSION['last_regeneration'] = time();
        } else if (time() - $_SESSION['last_regeneration'] > 300) { // 5 minutes
            session_regenerate_id(true);
            $_SESSION['last_regeneration'] = time();
        }
    }
}

function isLoggedIn() {
    startSession();
    return isset($_SESSION['admin_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        jsonResponse(['success' => false, 'error' => 'Authentication required'], 401);
    }
}

// Initialize database connection
$db = new Database();
$pdo = $db->getConnection();
?>
