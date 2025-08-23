<?php
// api/pdf-viewer.php - Serve PDF files with proper headers for iframe embedding
require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo 'Method not allowed';
    exit;
}

$filename = sanitizeInput($_GET['file'] ?? '');

if (!$filename) {
    http_response_code(400);
    echo 'Filename is required';
    exit;
}

// Security: Only allow files from the modules directory and with .pdf extension
if (!preg_match('/^[a-zA-Z0-9\-_\.]+\.pdf$/', $filename)) {
    http_response_code(400);
    echo 'Invalid filename';
    exit;
}

$filePath = __DIR__ . '/../public/modules/' . $filename;

if (!file_exists($filePath)) {
    http_response_code(404);
    echo 'File not found';
    exit;
}

// Set headers to allow iframe embedding and serve PDF
header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="' . $filename . '"');
header('Content-Length: ' . filesize($filePath));

// Override X-Frame-Options to allow same-origin framing
header('X-Frame-Options: SAMEORIGIN', true); // true parameter replaces any existing header

// Prevent caching issues
header('Cache-Control: public, max-age=3600');
header('Pragma: public');

// Output the PDF file
readfile($filePath);
?>
