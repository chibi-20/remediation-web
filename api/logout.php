<?php
// api/logout.php - Logout endpoint
require_once '../config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'error' => 'Method not allowed'], 405);
}

// Start session and destroy it
startSession();
session_destroy();

jsonResponse(['success' => true]);
?>
