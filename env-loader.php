<?php
/**
 * Environment Configuration Loader
 * Loads configuration from .env file
 */

class EnvLoader {
    private static $config = [];
    private static $loaded = false;

    /**
     * Load environment variables from .env file
     */
    public static function load($envFile = '.env') {
        if (self::$loaded) {
            return;
        }

        $envPath = __DIR__ . '/' . $envFile;
        
        if (!file_exists($envPath)) {
            // Fallback to default values if .env doesn't exist
            self::setDefaults();
            self::$loaded = true;
            return;
        }

        $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        foreach ($lines as $line) {
            // Skip comments
            if (strpos(trim($line), '#') === 0) {
                continue;
            }

            // Parse key=value pairs
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);
                
                // Remove quotes if present
                if (preg_match('/^["\'](.*)["\']\z/', $value, $matches)) {
                    $value = $matches[1];
                }
                
                self::$config[$key] = $value;
                
                // Also set as environment variable
                $_ENV[$key] = $value;
                putenv("$key=$value");
            }
        }

        self::$loaded = true;
    }

    /**
     * Get configuration value
     */
    public static function get($key, $default = null) {
        if (!self::$loaded) {
            self::load();
        }
        
        return self::$config[$key] ?? $default;
    }

    /**
     * Check if we're in production environment
     */
    public static function isProduction() {
        return self::get('ENVIRONMENT') === 'production';
    }

    /**
     * Check if debug mode is enabled
     */
    public static function isDebugMode() {
        return filter_var(self::get('DEBUG_MODE', false), FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Set default configuration values
     */
    private static function setDefaults() {
        self::$config = [
            'DB_HOST' => 'localhost',
            'DB_NAME' => 'remediation_web',
            'DB_USER' => 'root',
            'DB_PASS' => '',
            'ENVIRONMENT' => 'development',
            'SESSION_LIFETIME' => '3600',
            'SECURE_COOKIES' => 'false',
            'MAX_FILE_SIZE' => '10485760',
            'UPLOAD_PATH' => 'public/MODULES/',
            'APP_NAME' => 'SAGIP ARAL - Remediation Learning System',
            'APP_VERSION' => '1.0.0',
            'BASE_URL' => 'http://localhost/tms/remediation-web',
            'DEBUG_MODE' => 'true',
            'DISPLAY_ERRORS' => 'true',
            'ERROR_REPORTING' => 'E_ALL'
        ];
    }

    /**
     * Get all configuration as array
     */
    public static function all() {
        if (!self::$loaded) {
            self::load();
        }
        return self::$config;
    }
}

// Auto-load environment on include
EnvLoader::load();

// Set PHP error reporting based on environment
if (EnvLoader::isProduction()) {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}
?>
