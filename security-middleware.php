<?php
/**
 * Security Middleware
 * Provides comprehensive security checks for API endpoints
 */

require_once 'rate-limiter.php';
require_once 'env-loader.php';

class SecurityMiddleware {
    
    /**
     * Apply security checks to login endpoints
     */
    public static function checkLoginSecurity() {
        // Check rate limiting
        $rateCheck = RateLimiter::checkRateLimit('login');
        if (!$rateCheck['allowed']) {
            http_response_code(429);
            echo json_encode([
                'success' => false,
                'error' => $rateCheck['message'],
                'retry_after' => $rateCheck['retry_after']
            ]);
            exit;
        }
        
        // Apply general security headers
        self::setSecurityHeaders();
        
        // Validate request method
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Method not allowed']);
            exit;
        }
        
        // Check for CSRF protection in production
        if (EnvLoader::isProduction()) {
            self::checkCSRF();
        }
    }
    
    /**
     * Apply security checks to upload endpoints
     */
    public static function checkUploadSecurity() {
        // Check upload rate limiting
        $rateCheck = RateLimiter::checkRateLimit('upload');
        if (!$rateCheck['allowed']) {
            http_response_code(429);
            echo json_encode([
                'success' => false,
                'error' => $rateCheck['message'],
                'retry_after' => $rateCheck['retry_after']
            ]);
            exit;
        }
        
        // Apply security headers
        self::setSecurityHeaders();
        
        // Check authentication
        if (!self::isAuthenticated()) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Authentication required']);
            exit;
        }
        
        // Record upload attempt
        RateLimiter::recordRequest('upload');
    }
    
    /**
     * Apply security checks to API endpoints
     */
    public static function checkAPISecurity() {
        // Check API rate limiting
        $rateCheck = RateLimiter::checkRateLimit('api');
        if (!$rateCheck['allowed']) {
            http_response_code(429);
            echo json_encode([
                'success' => false,
                'error' => $rateCheck['message'],
                'retry_after' => $rateCheck['retry_after']
            ]);
            exit;
        }
        
        // Apply security headers
        self::setSecurityHeaders();
        
        // Record API request
        RateLimiter::recordRequest('api');
    }
    
    /**
     * Set comprehensive security headers
     */
    private static function setSecurityHeaders() {
        // Prevent XSS attacks
        header('X-XSS-Protection: 1; mode=block');
        
        // Prevent MIME type sniffing
        header('X-Content-Type-Options: nosniff');
        
        // Control framing (prevent clickjacking)
        header('X-Frame-Options: DENY');
        
        // Content Security Policy
        if (EnvLoader::isProduction()) {
            header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.tailwindcss.com; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com;");
        }
        
        // Strict Transport Security (HTTPS only)
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
        }
        
        // Referrer Policy
        header('Referrer-Policy: strict-origin-when-cross-origin');
        
        // Permissions Policy
        header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
    }
    
    /**
     * Check if user is authenticated
     */
    private static function isAuthenticated() {
        session_start();
        return isset($_SESSION['admin_id']) || isset($_SESSION['teacher_id']) || isset($_SESSION['student_id']);
    }
    
    /**
     * Basic CSRF protection
     */
    private static function checkCSRF() {
        session_start();
        
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        
        $submittedToken = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        
        if (!hash_equals($_SESSION['csrf_token'], $submittedToken)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'CSRF token mismatch']);
            exit;
        }
    }
    
    /**
     * Generate CSRF token for forms
     */
    public static function generateCSRFToken() {
        session_start();
        
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Log security event
     */
    public static function logSecurityEvent($event, $details = []) {
        if (!EnvLoader::isProduction()) {
            return; // Only log in production
        }
        
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'event' => $event,
            'details' => $details,
            'session_id' => session_id()
        ];
        
        $logDir = __DIR__ . '/logs/security/';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        $logFile = $logDir . date('Y-m-d') . '.log';
        file_put_contents($logFile, json_encode($logEntry) . "\n", FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Block suspicious IP addresses
     */
    public static function checkIPBlacklist() {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        $blacklistFile = __DIR__ . '/security/ip_blacklist.txt';
        
        if (file_exists($blacklistFile)) {
            $blacklist = file($blacklistFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            
            if (in_array($ip, $blacklist)) {
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => 'Access denied']);
                exit;
            }
        }
    }
}
?>
