<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Verification</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background-color: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        th { background-color: #f8f9fa; font-weight: bold; }
        .success { color: #28a745; font-weight: bold; }
        .info { color: #17a2b8; }
        .warning { color: #ffc107; }
        h1, h2 { color: #333; }
        .status-box { background: #e7f3ff; border: 1px solid #b3d9ff; padding: 15px; border-radius: 5px; margin: 15px 0; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Database Structure Verification</h1>
        
        <?php
        require_once '../config.php';
        
        try {
            $db = new Database();
            $pdo = $db->getConnection();
            
            echo '<div class="status-box">';
            echo '<h2 class="success">✅ Database Connection Successful</h2>';
            echo '</div>';
            
            // Show all tables
            $stmt = $pdo->query("SHOW TABLES");
            $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            echo '<h2>📋 Database Tables</h2>';
            echo '<div class="status-box">';
            echo '<p><strong>Available Tables:</strong> ' . implode(', ', $tables) . '</p>';
            echo '</div>';
            
            // Check each table
            foreach (['admins', 'teachers', 'students'] as $tableName) {
                if (in_array($tableName, $tables)) {
                    echo "<h2>🔍 {$tableName} Table</h2>";
                    
                    // Count records
                    $count = $pdo->query("SELECT COUNT(*) FROM $tableName")->fetchColumn();
                    echo "<p class='info'><strong>Records:</strong> $count</p>";
                    
                    // Show structure
                    $stmt = $pdo->query("DESCRIBE $tableName");
                    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    echo '<table>';
                    echo '<tr><th>Column</th><th>Type</th><th>Key</th><th>Default</th><th>Extra</th></tr>';
                    foreach ($columns as $column) {
                        echo '<tr>';
                        echo "<td>{$column['Field']}</td>";
                        echo "<td>{$column['Type']}</td>";
                        echo "<td>{$column['Key']}</td>";
                        echo "<td>{$column['Default']}</td>";
                        echo "<td>{$column['Extra']}</td>";
                        echo '</tr>';
                    }
                    echo '</table>';
                    
                    // Show sample data
                    if ($count > 0) {
                        echo "<h3>Sample Data (First 5 records)</h3>";
                        $stmt = $pdo->query("SELECT * FROM $tableName LIMIT 5");
                        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        
                        if (!empty($records)) {
                            echo '<table>';
                            echo '<tr>';
                            foreach (array_keys($records[0]) as $header) {
                                echo "<th>$header</th>";
                            }
                            echo '</tr>';
                            
                            foreach ($records as $record) {
                                echo '<tr>';
                                foreach ($record as $value) {
                                    $displayValue = strlen($value) > 50 ? substr($value, 0, 50) . '...' : $value;
                                    echo "<td>$displayValue</td>";
                                }
                                echo '</tr>';
                            }
                            echo '</table>';
                        }
                    }
                    
                    echo '<hr>';
                } else {
                    echo "<h2 class='warning'>⚠️ {$tableName} Table - NOT FOUND</h2>";
                }
            }
            
            // Verification summary
            echo '<div class="status-box">';
            echo '<h2 class="success">✅ Verification Summary</h2>';
            
            $adminCount = $pdo->query("SELECT COUNT(*) FROM admins")->fetchColumn();
            $teacherCount = $pdo->query("SELECT COUNT(*) FROM teachers")->fetchColumn();
            $studentCount = $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();
            
            echo "<ul>";
            echo "<li><strong>Admins:</strong> $adminCount (System administrators)</li>";
            echo "<li><strong>Teachers:</strong> $teacherCount (Subject teachers)</li>";
            echo "<li><strong>Students:</strong> $studentCount (Learners)</li>";
            echo "</ul>";
            
            if ($adminCount == 1 && $teacherCount > 0) {
                echo '<p class="success">🎉 Perfect! Database structure is properly separated with distinct roles.</p>';
            }
            echo '</div>';
            
        } catch (Exception $e) {
            echo '<div class="status-box">';
            echo '<h2 style="color: red;">❌ Error: ' . $e->getMessage() . '</h2>';
            echo '<pre>' . $e->getTraceAsString() . '</pre>';
            echo '</div>';
        }
        ?>
    </div>
</body>
</html>
