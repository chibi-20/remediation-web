<?php
/**
 * Secure File Upload Handler
 * Provides comprehensive security for file uploads
 */

class SecureFileUpload {
    
    private const ALLOWED_TYPES = [
        'pdf' => ['application/pdf'],
        'doc' => ['application/msword'],
        'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
        'ppt' => ['application/vnd.ms-powerpoint'],
        'pptx' => ['application/vnd.openxmlformats-officedocument.presentationml.presentation']
    ];
    
    private const MAX_FILE_SIZE = 10485760; // 10MB
    private const UPLOAD_DIR = 'public/MODULES/';
    
    /**
     * Validate and process file upload
     */
    public static function handleUpload($fileInput, $allowedExtensions = ['pdf']) {
        // Check if file was uploaded
        if (!isset($_FILES[$fileInput]) || $_FILES[$fileInput]['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('No file uploaded or upload error occurred');
        }
        
        $file = $_FILES[$fileInput];
        
        // Security validations
        self::validateFileSize($file);
        self::validateFileType($file, $allowedExtensions);
        self::validateFileName($file['name']);
        self::scanFileContent($file['tmp_name']);
        
        // Generate secure filename
        $secureFilename = self::generateSecureFilename($file['name']);
        
        // Create upload directory if it doesn't exist
        $uploadDir = __DIR__ . '/../' . self::UPLOAD_DIR;
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $uploadPath = $uploadDir . $secureFilename;
        
        // Move file to secure location
        if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
            throw new Exception('Failed to move uploaded file');
        }
        
        // Set secure file permissions
        chmod($uploadPath, 0644);
        
        return $secureFilename;
    }
    
    /**
     * Validate file size
     */
    private static function validateFileSize($file) {
        if ($file['size'] > self::MAX_FILE_SIZE) {
            throw new Exception('File size exceeds maximum allowed size of ' . (self::MAX_FILE_SIZE / 1024 / 1024) . 'MB');
        }
        
        if ($file['size'] <= 0) {
            throw new Exception('File appears to be empty');
        }
    }
    
    /**
     * Validate file type using multiple methods
     */
    private static function validateFileType($file, $allowedExtensions) {
        // Get file extension
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if (!in_array($extension, $allowedExtensions)) {
            throw new Exception('File type not allowed. Allowed types: ' . implode(', ', $allowedExtensions));
        }
        
        // Validate MIME type
        $mimeType = $file['type'];
        $validMimeTypes = [];
        
        foreach ($allowedExtensions as $ext) {
            if (isset(self::ALLOWED_TYPES[$ext])) {
                $validMimeTypes = array_merge($validMimeTypes, self::ALLOWED_TYPES[$ext]);
            }
        }
        
        if (!in_array($mimeType, $validMimeTypes)) {
            throw new Exception('Invalid file MIME type: ' . $mimeType);
        }
        
        // Additional MIME type check using finfo
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $detectedMimeType = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
            
            if (!in_array($detectedMimeType, $validMimeTypes)) {
                throw new Exception('File content does not match expected type');
            }
        }
    }
    
    /**
     * Validate filename for security
     */
    private static function validateFileName($filename) {
        // Check for dangerous characters
        if (preg_match('/[<>:"|?*]/', $filename)) {
            throw new Exception('Filename contains invalid characters');
        }
        
        // Check for path traversal attempts
        if (strpos($filename, '..') !== false || strpos($filename, '/') !== false || strpos($filename, '\\') !== false) {
            throw new Exception('Invalid filename: path traversal attempt detected');
        }
        
        // Check filename length
        if (strlen($filename) > 255) {
            throw new Exception('Filename too long');
        }
        
        // Check for executable extensions
        $dangerousExtensions = ['php', 'exe', 'bat', 'cmd', 'com', 'pif', 'scr', 'vbs', 'js', 'jar', 'sh'];
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($extension, $dangerousExtensions)) {
            throw new Exception('Potentially dangerous file type');
        }
    }
    
    /**
     * Scan file content for malicious code
     */
    private static function scanFileContent($filePath) {
        // Read beginning of file to check for malicious signatures
        $handle = fopen($filePath, 'rb');
        if ($handle) {
            $header = fread($handle, 1024);
            fclose($handle);
            
            // Check for PHP tags
            if (strpos($header, '<?php') !== false || strpos($header, '<?=') !== false) {
                throw new Exception('File contains potentially malicious PHP code');
            }
            
            // Check for script tags
            if (stripos($header, '<script') !== false) {
                throw new Exception('File contains potentially malicious script tags');
            }
            
            // Check for executable signatures
            $executableSignatures = [
                'MZ',      // Windows PE
                '\x7fELF', // Linux ELF
                'PK',      // ZIP (could contain executables)
            ];
            
            foreach ($executableSignatures as $signature) {
                if (strpos($header, $signature) === 0) {
                    // Allow ZIP for valid office documents
                    if ($signature === 'PK') {
                        // Additional validation for office documents would go here
                        continue;
                    }
                    throw new Exception('File appears to be an executable');
                }
            }
        }
    }
    
    /**
     * Generate secure filename
     */
    private static function generateSecureFilename($originalFilename) {
        $extension = strtolower(pathinfo($originalFilename, PATHINFO_EXTENSION));
        $basename = pathinfo($originalFilename, PATHINFO_FILENAME);
        
        // Sanitize basename
        $basename = preg_replace('/[^a-zA-Z0-9_-]/', '', $basename);
        $basename = substr($basename, 0, 50); // Limit length
        
        // Generate unique identifier
        $timestamp = time();
        $randomString = bin2hex(random_bytes(8));
        
        return $timestamp . '-' . $randomString . '-' . $basename . '.' . $extension;
    }
    
    /**
     * Delete uploaded file securely
     */
    public static function deleteFile($filename) {
        $filePath = __DIR__ . '/../' . self::UPLOAD_DIR . $filename;
        
        if (file_exists($filePath)) {
            // Overwrite file content before deletion for security
            $handle = fopen($filePath, 'r+');
            if ($handle) {
                $size = filesize($filePath);
                fwrite($handle, str_repeat('0', $size));
                fclose($handle);
            }
            
            return unlink($filePath);
        }
        
        return false;
    }
    
    /**
     * Get file info safely
     */
    public static function getFileInfo($filename) {
        $filePath = __DIR__ . '/../' . self::UPLOAD_DIR . $filename;
        
        if (!file_exists($filePath)) {
            return null;
        }
        
        return [
            'size' => filesize($filePath),
            'type' => mime_content_type($filePath),
            'modified' => filemtime($filePath),
            'readable' => is_readable($filePath)
        ];
    }
}
?>
