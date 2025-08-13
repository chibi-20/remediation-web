<?php
// Migration: Add password column to students table
require_once '../config.php';

try {
    $pdo->exec("ALTER TABLE students ADD COLUMN password VARCHAR(255) AFTER lrn");
    echo "Password column added to students table.";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
