<?php
// config-mysql.php - MySQL configuration version
define('DB_HOST', 'localhost');
define('DB_NAME', 'remediation_web');
define('DB_USER', 'root');
define('DB_PASS', '');

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

// Session management
function startSession() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
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
