<?php
// api/admins.php - Get all admins endpoint
require_once '../config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(['success' => false, 'error' => 'Method not allowed'], 405);
}

try {
    $stmt = $pdo->prepare("SELECT id, name, grade, subject, sections, username FROM admins");
    $stmt->execute();
    $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    jsonResponse($admins);
    
} catch (PDOException $e) {
    jsonResponse(['error' => 'Database error'], 500);
}
?>
