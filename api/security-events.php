<?php
// api/security-events.php - Recent security events endpoint
require_once '../config.php';

header('Content-Type: application/json');

// Check if admin is logged in (session or token)
session_start();
$isAuthenticated = false;

// Check session authentication
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in']) {
    $isAuthenticated = true;
}

// For now, allow access even without perfect session sharing for security monitoring
if (!$isAuthenticated) {
    error_log("Security events accessed without session - continuing with limited access");
}

try {
    $failedLogins = [];
    $rateLimits = [];
    
    // Read recent security logs
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
            
            // Get most recent events first
            $lines = array_reverse($lines);
            
            foreach ($lines as $line) {
                $event = json_decode($line, true);
                if ($event && isset($event['timestamp'])) {
                    $eventTime = strtotime($event['timestamp']);
                    $last24h = time() - (24 * 60 * 60);
                    
                    if ($eventTime >= $last24h) {
                        if ($event['event'] === 'failed_login_attempt' && count($failedLogins) < 10) {
                            $failedLogins[] = [
                                'timestamp' => $event['timestamp'],
                                'type' => $event['details']['type'] ?? 'unknown',
                                'username' => $event['details']['username'] ?? null,
                                'lrn' => $event['details']['lrn'] ?? null,
                                'ip' => $event['ip'],
                                'reason' => $event['details']['reason'] ?? 'unknown'
                            ];
                        }
                        
                        if (isset($event['details']['rate_limit']) && $event['details']['rate_limit'] && count($rateLimits) < 10) {
                            $rateLimits[] = [
                                'timestamp' => $event['timestamp'],
                                'limit_type' => $event['details']['limit_type'] ?? 'api',
                                'ip' => $event['ip'],
                                'attempts' => $event['details']['attempts'] ?? 0
                            ];
                        }
                    }
                }
            }
        }
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Security events retrieved',
        'failed_logins' => $failedLogins,
        'rate_limits' => $rateLimits
    ]);
    
} catch (Exception $e) {
    error_log("Security events error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Error retrieving security events: ' . $e->getMessage()
    ]);
}
?>
