<?php
// api/check-session.php - Check current session status
require_once '../config.php';

header('Content-Type: application/json');

session_start();

$response = [
    'success' => true,
    'session_data' => [
        'admin_logged_in' => $_SESSION['admin_logged_in'] ?? false,
        'admin_id' => $_SESSION['admin_id'] ?? null,
        'admin_username' => $_SESSION['admin_username'] ?? null,
        'student_id' => $_SESSION['student_id'] ?? null,
        'student_lrn' => $_SESSION['student_lrn'] ?? null,
        'teacher_id' => $_SESSION['teacher_id'] ?? null,
        'session_id' => session_id()
    ],
    'timestamp' => date('Y-m-d H:i:s')
];

echo json_encode($response, JSON_PRETTY_PRINT);
?>
