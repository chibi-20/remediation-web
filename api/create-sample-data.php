<?php
require_once '../config.php';

try {
    $db = new Database();
    $pdo = $db->getConnection();
    
    echo "🌱 Creating sample data for teachers and students...\n\n";
    
    // Sample teachers data
    $teachers = [
        [
            'username' => 'teacher001',
            'password' => 'teacher123',
            'name' => 'Maria Santos',
            'email' => 'maria.santos@school.edu.ph',
            'subject' => 'Mathematics',
            'grade' => 'Grade 10',
            'sections' => 'A, B, C'
        ],
        [
            'username' => 'teacher002',
            'password' => 'teacher123',
            'name' => 'John Dela Cruz',
            'email' => 'john.delacruz@school.edu.ph',
            'subject' => 'Science',
            'grade' => 'Grade 9',
            'sections' => 'A, B'
        ]
    ];
    
    echo "👩‍🏫 Creating teachers...\n";
    
    foreach ($teachers as $teacher) {
        // Check if teacher already exists
        $stmt = $pdo->prepare("SELECT id FROM teachers WHERE username = ?");
        $stmt->execute([$teacher['username']]);
        
        if (!$stmt->fetch()) {
            $hashedPassword = password_hash($teacher['password'], PASSWORD_DEFAULT);
            
            $insertStmt = $pdo->prepare("INSERT INTO teachers (username, password, name, email, subject, grade, sections, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
            $insertStmt->execute([
                $teacher['username'],
                $hashedPassword,
                $teacher['name'],
                $teacher['email'],
                $teacher['subject'],
                $teacher['grade'],
                $teacher['sections']
            ]);
            
            echo "✅ Created teacher: {$teacher['name']} ({$teacher['username']}) - {$teacher['subject']}\n";
        } else {
            echo "⏭️ Teacher {$teacher['username']} already exists\n";
        }
    }
    
    // Get teacher IDs for student assignment
    $stmt = $pdo->query("SELECT id, name FROM teachers WHERE username IN ('teacher001', 'teacher002')");
    $teacherIds = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "\n👨‍🎓 Creating students...\n";
    
    // Sample students data
    $students = [
        [
            'username' => 'student001',
            'password' => 'student123',
            'firstName' => 'Juan',
            'lastName' => 'Reyes',
            'name' => 'Juan Reyes',
            'section' => 'Grade 10-A',
            'grade' => 'Grade 10',
            'lrn' => '123456789001',
            'teacher_id' => $teacherIds[0]['id'] ?? null
        ],
        [
            'username' => 'student002',
            'password' => 'student123',
            'firstName' => 'Anna',
            'lastName' => 'Garcia',
            'name' => 'Anna Garcia',
            'section' => 'Grade 10-A',
            'grade' => 'Grade 10',
            'lrn' => '123456789002',
            'teacher_id' => $teacherIds[0]['id'] ?? null
        ],
        [
            'username' => 'student003',
            'password' => 'student123',
            'firstName' => 'Miguel',
            'lastName' => 'Torres',
            'name' => 'Miguel Torres',
            'section' => 'Grade 10-B',
            'grade' => 'Grade 10',
            'lrn' => '123456789003',
            'teacher_id' => $teacherIds[0]['id'] ?? null
        ],
        [
            'username' => 'student004',
            'password' => 'student123',
            'firstName' => 'Sofia',
            'lastName' => 'Mendoza',
            'name' => 'Sofia Mendoza',
            'section' => 'Grade 10-B',
            'grade' => 'Grade 10',
            'lrn' => '123456789004',
            'teacher_id' => $teacherIds[0]['id'] ?? null
        ],
        [
            'username' => 'student005',
            'password' => 'student123',
            'firstName' => 'Carlos',
            'lastName' => 'Villanueva',
            'name' => 'Carlos Villanueva',
            'section' => 'Grade 10-C',
            'grade' => 'Grade 10',
            'lrn' => '123456789005',
            'teacher_id' => $teacherIds[0]['id'] ?? null
        ],
        [
            'username' => 'student006',
            'password' => 'student123',
            'firstName' => 'Isabella',
            'lastName' => 'Cruz',
            'name' => 'Isabella Cruz',
            'section' => 'Grade 9-A',
            'grade' => 'Grade 9',
            'lrn' => '123456789006',
            'teacher_id' => $teacherIds[1]['id'] ?? null
        ],
        [
            'username' => 'student007',
            'password' => 'student123',
            'firstName' => 'Diego',
            'lastName' => 'Morales',
            'name' => 'Diego Morales',
            'section' => 'Grade 9-A',
            'grade' => 'Grade 9',
            'lrn' => '123456789007',
            'teacher_id' => $teacherIds[1]['id'] ?? null
        ],
        [
            'username' => 'student008',
            'password' => 'student123',
            'firstName' => 'Camila',
            'lastName' => 'Ramos',
            'name' => 'Camila Ramos',
            'section' => 'Grade 9-B',
            'grade' => 'Grade 9',
            'lrn' => '123456789008',
            'teacher_id' => $teacherIds[1]['id'] ?? null
        ],
        [
            'username' => 'student009',
            'password' => 'student123',
            'firstName' => 'Adrian',
            'lastName' => 'Santos',
            'name' => 'Adrian Santos',
            'section' => 'Grade 9-B',
            'grade' => 'Grade 9',
            'lrn' => '123456789009',
            'teacher_id' => $teacherIds[1]['id'] ?? null
        ],
        [
            'username' => 'student010',
            'password' => 'student123',
            'firstName' => 'Lucia',
            'lastName' => 'Hernandez',
            'name' => 'Lucia Hernandez',
            'section' => 'Grade 10-A',
            'grade' => 'Grade 10',
            'lrn' => '123456789010',
            'teacher_id' => $teacherIds[0]['id'] ?? null
        ]
    ];
    
    foreach ($students as $student) {
        // Check if student already exists
        $stmt = $pdo->prepare("SELECT id FROM students WHERE username = ? OR lrn = ?");
        $stmt->execute([$student['username'], $student['lrn']]);
        
        if (!$stmt->fetch()) {
            $hashedPassword = password_hash($student['password'], PASSWORD_DEFAULT);
            
            $insertStmt = $pdo->prepare("INSERT INTO students (username, password, firstName, lastName, name, section, grade, lrn, teacher_id, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
            $insertStmt->execute([
                $student['username'],
                $hashedPassword,
                $student['firstName'],
                $student['lastName'],
                $student['name'],
                $student['section'],
                $student['grade'],
                $student['lrn'],
                $student['teacher_id']
            ]);
            
            $teacherName = '';
            foreach ($teacherIds as $teacher) {
                if ($teacher['id'] == $student['teacher_id']) {
                    $teacherName = " (Teacher: {$teacher['name']})";
                    break;
                }
            }
            
            echo "✅ Created student: {$student['name']} ({$student['username']}) - {$student['section']}{$teacherName}\n";
        } else {
            echo "⏭️ Student {$student['username']} already exists\n";
        }
    }
    
    echo "\n📊 Sample data creation complete!\n\n";
    
    // Show summary
    $teacherCount = $pdo->query("SELECT COUNT(*) FROM teachers")->fetchColumn();
    $studentCount = $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();
    
    echo "📈 Database Summary:\n";
    echo "• Teachers: $teacherCount\n";
    echo "• Students: $studentCount\n";
    
    echo "\n👥 Teacher-Student Distribution:\n";
    $stmt = $pdo->query("
        SELECT t.name as teacher_name, t.subject, t.grade, COUNT(s.id) as student_count
        FROM teachers t
        LEFT JOIN students s ON t.id = s.teacher_id
        GROUP BY t.id, t.name, t.subject, t.grade
        ORDER BY t.name
    ");
    $distribution = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($distribution as $row) {
        echo "• {$row['teacher_name']} ({$row['subject']}, {$row['grade']}): {$row['student_count']} students\n";
    }
    
    echo "\n🔑 Login Credentials:\n";
    echo "Teachers:\n";
    echo "• Username: teacher001, Password: teacher123 (Maria Santos - Mathematics)\n";
    echo "• Username: teacher002, Password: teacher123 (John Dela Cruz - Science)\n";
    echo "\nStudents:\n";
    echo "• Username: student001-010, Password: student123\n";
    echo "\nAdmin:\n";
    echo "• Username: 307901, Password: (your admin password)\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
?>
