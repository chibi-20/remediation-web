<?php
// check-sqlite.php - Check contents of students.db SQLite file
echo "<h1>SQLite Database Contents (students.db)</h1>";

$sqliteFile = 'students.db';

if (!file_exists($sqliteFile)) {
    echo "<p style='color: red;'>❌ SQLite file 'students.db' not found!</p>";
    exit;
}

echo "<p>File size: " . number_format(filesize($sqliteFile)) . " bytes</p>";
echo "<p>Last modified: " . date('Y-m-d H:i:s', filemtime($sqliteFile)) . "</p>";

try {
    $sqlite = new PDO("sqlite:$sqliteFile");
    $sqlite->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>Tables in SQLite Database:</h2>";
    
    // Get all tables
    $stmt = $sqlite->query("SELECT name FROM sqlite_master WHERE type='table'");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($tables)) {
        echo "<p>No tables found in the database.</p>";
    } else {
        echo "<ul>";
        foreach ($tables as $table) {
            echo "<li><strong>$table</strong></li>";
        }
        echo "</ul>";
        
        // Check data in each table
        foreach ($tables as $table) {
            echo "<h3>Data in table: $table</h3>";
            try {
                $stmt = $sqlite->query("SELECT COUNT(*) as count FROM $table");
                $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
                echo "<p>Records in $table: $count</p>";
                
                if ($count > 0 && $count <= 20) {
                    // Show sample data for small tables
                    $stmt = $sqlite->query("SELECT * FROM $table LIMIT 10");
                    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
                    if (!empty($rows)) {
                        // Headers
                        echo "<tr style='background: #f0f0f0;'>";
                        foreach (array_keys($rows[0]) as $header) {
                            echo "<th style='padding: 8px; border: 1px solid #ddd;'>$header</th>";
                        }
                        echo "</tr>";
                        
                        // Data rows
                        foreach ($rows as $row) {
                            echo "<tr>";
                            foreach ($row as $value) {
                                $displayValue = $value;
                                if (strlen($value) > 50) {
                                    $displayValue = substr($value, 0, 50) . "...";
                                }
                                echo "<td style='padding: 8px; border: 1px solid #ddd;'>$displayValue</td>";
                            }
                            echo "</tr>";
                        }
                    }
                    echo "</table>";
                }
            } catch (Exception $e) {
                echo "<p style='color: red;'>Error reading table $table: " . $e->getMessage() . "</p>";
            }
        }
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ SQLite connection failed: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h2>Current System Configuration</h2>";
echo "<p><a href='switch-database.php?action=test'>Test Current Database Connection</a></p>";
echo "<p><a href='switch-database.php'>Database Switcher</a></p>";
?>
