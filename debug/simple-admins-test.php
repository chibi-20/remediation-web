<?php
// simple-admins-test.php - Simple test for admins API
require_once 'config.php';

header('Content-Type: application/json');

try {
    $stmt = $pdo->prepare("SELECT id, name, grade, subject, username FROM admins");
    $stmt->execute();
    $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($admins);
    
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
