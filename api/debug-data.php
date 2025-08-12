<?php
// Debug script to check database data
require_once '../config.php';

echo "<h3>Students Table:</h3>";
$stmt = $pdo->prepare("SELECT * FROM students");
$stmt->execute();
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "<pre>";
print_r($students);
echo "</pre>";

echo "<h3>Modules Table:</h3>";
$stmt = $pdo->prepare("SELECT * FROM modules");
$stmt->execute();
$modules = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "<pre>";
print_r($modules);
echo "</pre>";

echo "<h3>Admins Table:</h3>";
$stmt = $pdo->prepare("SELECT * FROM admins");
$stmt->execute();
$admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "<pre>";
print_r($admins);
echo "</pre>";
?>
