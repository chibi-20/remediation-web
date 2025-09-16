<?php
/**
 * CSRF Token API Endpoint
 * Provides CSRF tokens for frontend forms
 */

require_once '../config.php';
require_once '../security-middleware.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Apply basic security
SecurityMiddleware::setSecurityHeaders();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

try {
    // Generate or retrieve CSRF token
    $csrfToken = SecurityMiddleware::generateCSRFToken();
    
    echo json_encode([
        'success' => true,
        'csrf_token' => $csrfToken
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to generate CSRF token'
    ]);
}
?>