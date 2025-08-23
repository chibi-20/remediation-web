<?php
/**
 * Rate Limiting System
 * Prevents brute force attacks and API abuse
 */

class RateLimiter {
    
    private const MAX_REQUESTS_PER_MINUTE = 30;
    private const MAX_REQUESTS_PER_HOUR = 500;
    private const MAX_LOGIN_ATTEMPTS = 5;
    private const LOGIN_LOCKOUT_TIME = 900; // 15 minutes
    private const UPLOAD_RATE_LIMIT = 5; // 5 uploads per hour
    
    /**
     * Check if IP is rate limited
     */
    public static function checkRateLimit($action = 'general') {
        $ip = self::getClientIP();
        $currentTime = time();
        
        switch ($action) {
            case 'login':
                return self::checkLoginAttempts($ip, $currentTime);
            case 'upload':
                return self::checkUploadRate($ip, $currentTime);
            case 'api':
                return self::checkAPIRate($ip, $currentTime);
            default:
                return self::checkGeneralRate($ip, $currentTime);
        }
    }
    
    /**
     * Record failed login attempt
     */
    public static function recordFailedLogin() {
        $ip = self::getClientIP();
        $attempts = self::getAttempts($ip, 'login');
        $attempts[] = time();
        
        // Keep only recent attempts (last 15 minutes)
        $attempts = array_filter($attempts, function($timestamp) {
            return (time() - $timestamp) < self::LOGIN_LOCKOUT_TIME;
        });
        
        self::saveAttempts($ip, 'login', $attempts);
        
        return count($attempts);
    }
    
    /**
     * Check login attempts rate limit
     */
    private static function checkLoginAttempts($ip, $currentTime) {
        $attempts = self::getAttempts($ip, 'login');
        
        // Remove old attempts
        $recentAttempts = array_filter($attempts, function($timestamp) use ($currentTime) {
            return ($currentTime - $timestamp) < self::LOGIN_LOCKOUT_TIME;
        });
        
        if (count($recentAttempts) >= self::MAX_LOGIN_ATTEMPTS) {
            return [
                'allowed' => false,
                'message' => 'Too many login attempts. Please try again in 15 minutes.',
                'retry_after' => self::LOGIN_LOCKOUT_TIME
            ];
        }
        
        return ['allowed' => true];
    }
    
    /**
     * Check upload rate limit
     */
    private static function checkUploadRate($ip, $currentTime) {
        $uploads = self::getAttempts($ip, 'upload');
        
        // Remove uploads older than 1 hour
        $recentUploads = array_filter($uploads, function($timestamp) use ($currentTime) {
            return ($currentTime - $timestamp) < 3600;
        });
        
        if (count($recentUploads) >= self::UPLOAD_RATE_LIMIT) {
            return [
                'allowed' => false,
                'message' => 'Upload rate limit exceeded. Please wait before uploading again.',
                'retry_after' => 3600
            ];
        }
        
        return ['allowed' => true];
    }
    
    /**
     * Check API rate limit
     */
    private static function checkAPIRate($ip, $currentTime) {
        $requests = self::getAttempts($ip, 'api');
        
        // Check requests per minute
        $lastMinute = array_filter($requests, function($timestamp) use ($currentTime) {
            return ($currentTime - $timestamp) < 60;
        });
        
        if (count($lastMinute) >= self::MAX_REQUESTS_PER_MINUTE) {
            return [
                'allowed' => false,
                'message' => 'Rate limit exceeded. Too many requests per minute.',
                'retry_after' => 60
            ];
        }
        
        // Check requests per hour
        $lastHour = array_filter($requests, function($timestamp) use ($currentTime) {
            return ($currentTime - $timestamp) < 3600;
        });
        
        if (count($lastHour) >= self::MAX_REQUESTS_PER_HOUR) {
            return [
                'allowed' => false,
                'message' => 'Rate limit exceeded. Too many requests per hour.',
                'retry_after' => 3600
            ];
        }
        
        return ['allowed' => true];
    }
    
    /**
     * Check general rate limit
     */
    private static function checkGeneralRate($ip, $currentTime) {
        return self::checkAPIRate($ip, $currentTime);
    }
    
    /**
     * Record successful request
     */
    public static function recordRequest($action = 'api') {
        $ip = self::getClientIP();
        $attempts = self::getAttempts($ip, $action);
        $attempts[] = time();
        
        // Clean old attempts based on action type
        switch ($action) {
            case 'upload':
                $cutoff = 3600; // 1 hour
                break;
            case 'login':
                $cutoff = self::LOGIN_LOCKOUT_TIME;
                break;
            default:
                $cutoff = 3600; // 1 hour
        }
        
        $attempts = array_filter($attempts, function($timestamp) use ($cutoff) {
            return (time() - $timestamp) < $cutoff;
        });
        
        self::saveAttempts($ip, $action, $attempts);
    }
    
    /**
     * Get client IP address
     */
    private static function getClientIP() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            return $_SERVER['REMOTE_ADDR'];
        }
    }
    
    /**
     * Get stored attempts for IP and action
     */
    private static function getAttempts($ip, $action) {
        $filename = self::getStorageFile($ip, $action);
        
        if (!file_exists($filename)) {
            return [];
        }
        
        $data = file_get_contents($filename);
        return $data ? json_decode($data, true) : [];
    }
    
    /**
     * Save attempts for IP and action
     */
    private static function saveAttempts($ip, $action, $attempts) {
        $filename = self::getStorageFile($ip, $action);
        $dir = dirname($filename);
        
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        
        file_put_contents($filename, json_encode($attempts));
    }
    
    /**
     * Get storage filename for IP and action
     */
    private static function getStorageFile($ip, $action) {
        $hashedIP = md5($ip . 'salt_for_security');
        return __DIR__ . '/tmp/rate_limit/' . $hashedIP . '_' . $action . '.json';
    }
    
    /**
     * Clean old rate limit files
     */
    public static function cleanup() {
        $dir = __DIR__ . '/tmp/rate_limit/';
        if (!is_dir($dir)) {
            return;
        }
        
        $files = glob($dir . '*.json');
        $currentTime = time();
        
        foreach ($files as $file) {
            $fileAge = $currentTime - filemtime($file);
            
            // Delete files older than 24 hours
            if ($fileAge > 86400) {
                unlink($file);
            }
        }
    }
}
?>
