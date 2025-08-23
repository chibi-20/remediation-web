<?php
// api/manage-blacklist.php - IP blacklist management endpoint
require_once '../config.php';

header('Content-Type: application/json');

// Check if admin is logged in (session or token)
session_start();
$isAuthenticated = false;

// Check session authentication
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in']) {
    $isAuthenticated = true;
}

// For now, allow read access even without perfect session sharing
if (!$isAuthenticated && $_SERVER['REQUEST_METHOD'] === 'POST') {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Admin authentication required for modifications']);
    exit;
}

if (!$isAuthenticated) {
    error_log("Blacklist accessed without session - allowing read-only access");
}

try {
    $blacklistFile = __DIR__ . '/../security/ip_blacklist.txt';
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        $action = $input['action'] ?? '';
        $ip = trim($input['ip'] ?? '');
        
        if (empty($ip) || !filter_var($ip, FILTER_VALIDATE_IP)) {
            echo json_encode(['success' => false, 'error' => 'Invalid IP address']);
            exit;
        }
        
        if ($action === 'add') {
            // Read current blacklist
            $blacklist = [];
            if (file_exists($blacklistFile)) {
                $blacklist = file($blacklistFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            }
            
            // Check if IP is already blacklisted
            if (in_array($ip, $blacklist)) {
                echo json_encode(['success' => false, 'error' => 'IP address is already blacklisted']);
                exit;
            }
            
            // Add IP to blacklist
            $blacklist[] = $ip;
            file_put_contents($blacklistFile, implode("\n", $blacklist) . "\n", LOCK_EX);
            
            echo json_encode(['success' => true, 'message' => 'IP address added to blacklist']);
            
        } elseif ($action === 'remove') {
            // Read current blacklist
            $blacklist = [];
            if (file_exists($blacklistFile)) {
                $blacklist = file($blacklistFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            }
            
            // Remove IP from blacklist
            $blacklist = array_filter($blacklist, function($blockedIp) use ($ip) {
                return trim($blockedIp) !== $ip;
            });
            
            file_put_contents($blacklistFile, implode("\n", $blacklist) . "\n", LOCK_EX);
            
            echo json_encode(['success' => true, 'message' => 'IP address removed from blacklist']);
            
        } else {
            echo json_encode(['success' => false, 'error' => 'Invalid action']);
        }
        
    } elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Return current blacklist
        $blacklist = [];
        if (file_exists($blacklistFile)) {
            $blacklist = file($blacklistFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            $blacklist = array_filter($blacklist, function($ip) {
                return !empty(trim($ip)) && !str_starts_with(trim($ip), '#');
            });
        }
        
        echo json_encode(['success' => true, 'message' => 'Blacklist retrieved', 'blacklist' => array_values($blacklist)]);
        
    } else {
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    }
    
} catch (Exception $e) {
    error_log("Blacklist management error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Error managing IP blacklist']);
}
?>
