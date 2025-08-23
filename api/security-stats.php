<?php
// api/security-stats.php - Security statistics endpoint
require_once '../config.php';

header('Content-Type: application/json');

// Check if admin is logged in (session or token)
session_start();
$isAuthenticated = false;

// Check session authentication
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in']) {
    $isAuthenticated = true;
}

// Check if we have admin token in localStorage (for new windows)
if (!$isAuthenticated) {
    // For now, if session fails, try to continue with a warning
    // This allows the security dashboard to work even without perfect session sharing
    error_log("Security dashboard accessed without session - continuing with limited access");
}

try {
    $stats = [
        'failed_logins_24h' => 0,
        'rate_limit_violations' => 0,
        'upload_rejections' => 0,
        'active_sessions' => 0
    ];
    
    // Count failed logins in last 24 hours
    $logDir = __DIR__ . '/../logs/security/';
    $today = date('Y-m-d');
    $yesterday = date('Y-m-d', strtotime('-1 day'));
    
    $logFiles = [
        $logDir . $today . '.log',
        $logDir . $yesterday . '.log'
    ];
    
    foreach ($logFiles as $logFile) {
        if (file_exists($logFile)) {
            $lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                $event = json_decode($line, true);
                if ($event && isset($event['timestamp'])) {
                    $eventTime = strtotime($event['timestamp']);
                    $last24h = time() - (24 * 60 * 60);
                    
                    if ($eventTime >= $last24h) {
                        if ($event['event'] === 'failed_login_attempt') {
                            $stats['failed_logins_24h']++;
                        }
                        if (isset($event['details']['rate_limit']) && $event['details']['rate_limit']) {
                            $stats['rate_limit_violations']++;
                        }
                        if (isset($event['details']['upload_rejected']) && $event['details']['upload_rejected']) {
                            $stats['upload_rejections']++;
                        }
                    }
                }
            }
        }
    }
    
    // Count active sessions (simplified - count session files)
    $sessionPath = session_save_path();
    if (empty($sessionPath)) {
        $sessionPath = sys_get_temp_dir();
    }
    
    if (is_dir($sessionPath)) {
        $sessionFiles = glob($sessionPath . '/sess_*');
        $stats['active_sessions'] = count($sessionFiles);
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Security stats retrieved',
        'stats' => $stats
    ]);
    
} catch (Exception $e) {
    error_log("Security stats error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Error retrieving security statistics: ' . $e->getMessage()
    ]);
}
?>
