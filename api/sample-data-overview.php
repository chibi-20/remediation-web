<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sample Data Overview</title>
    <style>
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            margin: 0; 
            padding: 20px; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .container { 
            max-width: 1400px; 
            margin: 0 auto; 
            background: white; 
            padding: 30px; 
            border-radius: 15px; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        h1 { 
            color: #333; 
            text-align: center; 
            margin-bottom: 30px;
            font-size: 2.5em;
        }
        h2 { 
            color: #555; 
            border-bottom: 3px solid #667eea;
            padding-bottom: 10px;
            margin-top: 40px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 25px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .stat-number {
            font-size: 3em;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .stat-label {
            font-size: 1.1em;
            opacity: 0.9;
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin: 20px 0; 
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            border-radius: 10px;
            overflow: hidden;
        }
        th { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px; 
            text-align: left; 
            font-weight: 600;
        }
        td { 
            padding: 12px 15px; 
            border-bottom: 1px solid #eee;
        }
        tr:nth-child(even) { 
            background-color: #f8f9fa; 
        }
        tr:hover { 
            background-color: #e3f2fd; 
        }
        .badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.8em;
            font-weight: bold;
        }
        .badge-math { background: #e3f2fd; color: #1976d2; }
        .badge-science { background: #e8f5e8; color: #388e3c; }
        .badge-grade10 { background: #fff3e0; color: #f57c00; }
        .badge-grade9 { background: #fce4ec; color: #c2185b; }
        .login-section {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 10px;
            margin: 30px 0;
            border-left: 5px solid #667eea;
        }
        .login-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }
        .login-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🎓 Sample Data Overview</h1>
        
        <?php
        require_once '../config.php';
        
        try {
            $db = new Database();
            $pdo = $db->getConnection();
            
            // Get counts
            $adminCount = $pdo->query("SELECT COUNT(*) FROM admins")->fetchColumn();
            $teacherCount = $pdo->query("SELECT COUNT(*) FROM teachers")->fetchColumn();
            $studentCount = $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();
            
            echo '<div class="stats-grid">';
            echo '<div class="stat-card">';
            echo '<div class="stat-number">' . $adminCount . '</div>';
            echo '<div class="stat-label">System Admins</div>';
            echo '</div>';
            echo '<div class="stat-card">';
            echo '<div class="stat-number">' . $teacherCount . '</div>';
            echo '<div class="stat-label">Teachers</div>';
            echo '</div>';
            echo '<div class="stat-card">';
            echo '<div class="stat-number">' . $studentCount . '</div>';
            echo '<div class="stat-label">Students</div>';
            echo '</div>';
            echo '</div>';
            
            // Teachers table
            echo '<h2>👩‍🏫 Teachers</h2>';
            $stmt = $pdo->query("SELECT * FROM teachers ORDER BY name");
            $teachers = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (!empty($teachers)) {
                echo '<table>';
                echo '<tr><th>Name</th><th>Username</th><th>Subject</th><th>Grade</th><th>Sections</th><th>Email</th><th>Created</th></tr>';
                foreach ($teachers as $teacher) {
                    $subjectBadge = $teacher['subject'] == 'Mathematics' ? 'badge-math' : 'badge-science';
                    $gradeBadge = $teacher['grade'] == 'Grade 10' ? 'badge-grade10' : 'badge-grade9';
                    
                    echo '<tr>';
                    echo '<td><strong>' . $teacher['name'] . '</strong></td>';
                    echo '<td>' . $teacher['username'] . '</td>';
                    echo '<td><span class="badge ' . $subjectBadge . '">' . $teacher['subject'] . '</span></td>';
                    echo '<td><span class="badge ' . $gradeBadge . '">' . $teacher['grade'] . '</span></td>';
                    echo '<td>' . $teacher['sections'] . '</td>';
                    echo '<td>' . $teacher['email'] . '</td>';
                    echo '<td>' . date('M j, Y', strtotime($teacher['created_at'])) . '</td>';
                    echo '</tr>';
                }
                echo '</table>';
            }
            
            // Students table
            echo '<h2>👨‍🎓 Students</h2>';
            $stmt = $pdo->query("
                SELECT s.*, t.name as teacher_name, t.subject as teacher_subject
                FROM students s
                LEFT JOIN teachers t ON s.teacher_id = t.id
                ORDER BY s.name
            ");
            $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (!empty($students)) {
                echo '<table>';
                echo '<tr><th>Name</th><th>Username</th><th>Grade</th><th>Section</th><th>LRN</th><th>Teacher</th><th>Subject</th><th>Created</th></tr>';
                foreach ($students as $student) {
                    $gradeBadge = $student['grade'] == 'Grade 10' ? 'badge-grade10' : 'badge-grade9';
                    $subjectBadge = $student['teacher_subject'] == 'Mathematics' ? 'badge-math' : 'badge-science';
                    
                    echo '<tr>';
                    echo '<td><strong>' . $student['name'] . '</strong></td>';
                    echo '<td>' . $student['username'] . '</td>';
                    echo '<td><span class="badge ' . $gradeBadge . '">' . $student['grade'] . '</span></td>';
                    echo '<td>' . $student['section'] . '</td>';
                    echo '<td>' . $student['lrn'] . '</td>';
                    echo '<td>' . ($student['teacher_name'] ?? 'Not assigned') . '</td>';
                    echo '<td>';
                    if ($student['teacher_subject']) {
                        echo '<span class="badge ' . $subjectBadge . '">' . $student['teacher_subject'] . '</span>';
                    } else {
                        echo '-';
                    }
                    echo '</td>';
                    echo '<td>' . date('M j, Y', strtotime($student['created_at'])) . '</td>';
                    echo '</tr>';
                }
                echo '</table>';
            }
            
            // Login credentials
            echo '<div class="login-section">';
            echo '<h2>🔑 Login Credentials for Testing</h2>';
            echo '<div class="login-grid">';
            
            echo '<div class="login-card">';
            echo '<h3>👑 System Admin</h3>';
            echo '<p><strong>Username:</strong> 307901</p>';
            echo '<p><strong>Password:</strong> (your admin password)</p>';
            echo '<p><strong>Access:</strong> Full system administration</p>';
            echo '</div>';
            
            echo '<div class="login-card">';
            echo '<h3>👩‍🏫 Teachers</h3>';
            echo '<p><strong>Maria Santos (Math):</strong><br>';
            echo 'Username: teacher001<br>';
            echo 'Password: teacher123</p>';
            echo '<p><strong>John Dela Cruz (Science):</strong><br>';
            echo 'Username: teacher002<br>';
            echo 'Password: teacher123</p>';
            echo '</div>';
            
            echo '<div class="login-card">';
            echo '<h3>👨‍🎓 Students</h3>';
            echo '<p><strong>All Students:</strong><br>';
            echo 'Username: student001 to student010<br>';
            echo 'Password: student123</p>';
            echo '<p><em>Examples:</em><br>';
            echo '• Juan Reyes: student001<br>';
            echo '• Anna Garcia: student002<br>';
            echo '• Isabella Cruz: student006</p>';
            echo '</div>';
            
            echo '</div>';
            echo '</div>';
            
        } catch (Exception $e) {
            echo '<div style="color: red; padding: 20px; background: #ffebee; border-radius: 10px;">';
            echo '<h2>❌ Error</h2>';
            echo '<p>' . $e->getMessage() . '</p>';
            echo '</div>';
        }
        ?>
        
        <div style="text-align: center; margin-top: 40px; padding: 20px; background: #f8f9fa; border-radius: 10px;">
            <h3>🚀 Ready to Test!</h3>
            <p>Your database now has sample data. You can:</p>
            <ul style="text-align: left; display: inline-block;">
                <li>Test the admin dashboard with teacher and student management</li>
                <li>Login as different teachers to see their interfaces</li>
                <li>Login as students to test the learning modules</li>
                <li>Modify teacher-student relationships as needed</li>
            </ul>
        </div>
    </div>
</body>
</html>
