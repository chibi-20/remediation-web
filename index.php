<?php
// index.php - Main entry point for the application
require_once 'config.php';

// Handle routing based on request
$request = $_SERVER['REQUEST_URI'];
$path = parse_url($request, PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// Remove base path if running in subdirectory
$basePath = '/tms/remediation-web';
if (strpos($path, $basePath) === 0) {
    $path = substr($path, strlen($basePath));
}

// Route handling
switch ($path) {
    case '/':
    case '/index.php':
        header('Location: public/index.html');
        exit;
        
    case '/admin':
        header('Location: public/teacher-dashboard.html');
        exit;
        
    // API Routes
    case '/api/register-admin':
        if ($method === 'POST') {
            require 'api/register-admin.php';
        }
        break;
        
    case '/admin-login':
        if ($method === 'POST') {
            require 'api/admin-login.php';
        }
        break;
        
    case '/api/logout':
        if ($method === 'POST') {
            require 'api/logout.php';
        }
        break;
        
    case '/api/register-student':
        if ($method === 'POST') {
            require 'api/register-student.php';
        }
        break;
        
    case '/api/student-login':
        if ($method === 'POST') {
            require 'api/student-login.php';
        }
        break;
        
    case '/students':
        if ($method === 'GET') {
            require 'api/students.php';
        }
        break;
        
    case '/update-progress':
        if ($method === 'POST') {
            require 'api/update-progress.php';
        }
        break;
        
    case '/api/create-module':
        if ($method === 'POST') {
            require 'api/create-module.php';
        }
        break;
        
    case '/api/modules':
        if ($method === 'GET') {
            require 'api/modules.php';
        }
        break;
        
    default:
        // Handle update-module with ID
        if (preg_match('/^\/api\/update-module\/(\d+)$/', $path, $matches)) {
            if ($method === 'POST') {
                $_GET['id'] = $matches[1];
                require 'api/update-module.php';
            }
        } else {
            // Serve static files from public directory
            $filePath = __DIR__ . '/public' . $path;
            if (file_exists($filePath) && is_file($filePath)) {
                $mimeTypes = [
                    'html' => 'text/html',
                    'css' => 'text/css',
                    'js' => 'application/javascript',
                    'pdf' => 'application/pdf',
                    'png' => 'image/png',
                    'jpg' => 'image/jpeg',
                    'jpeg' => 'image/jpeg',
                    'gif' => 'image/gif'
                ];
                
                $extension = pathinfo($filePath, PATHINFO_EXTENSION);
                $mimeType = $mimeTypes[$extension] ?? 'application/octet-stream';
                
                header('Content-Type: ' . $mimeType);
                readfile($filePath);
                exit;
            } else {
                http_response_code(404);
                echo "404 - Page not found";
            }
        }
        break;
}
?>
